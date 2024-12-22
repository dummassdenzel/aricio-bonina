<?php

require_once 'global.php';
require_once __DIR__ . '/../src/Mailer.php';

class Post extends GlobalMethods
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        parent::__construct();
        $this->pdo = $pdo;
    }

    public function getRequestData()
    {
        return $this->encryption->processRequestData();
    }

    private function ensureUploadDirectory()
    {
        $uploadDir = "uploads";
        $tenantIdsDir = "uploads/tenant_ids";

        // Create base uploads directory with full permissions
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create uploads directory");
            }
            chmod($uploadDir, 0777);
        }

        // Create tenant_ids subdirectory with full permissions
        if (!file_exists($tenantIdsDir)) {
            if (!mkdir($tenantIdsDir, 0777, true)) {
                throw new Exception("Failed to create tenant_ids directory");
            }
            chmod($tenantIdsDir, 0777);
        }
    }

    public function userLogin($data)
    {
        try {
            // CHECK IF USER EXISTS
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($data->password, $user['password_hash'])) {
                return $this->sendPayload(null, "failed", "Invalid email or password", 401);
            }

            // GENERATE JWT TOKEN
            $jwt = new Jwt();
            $payload = [
                "id" => $user['id'],
                "firstName" => $user['first_name'],
                "lastName" => $user['last_name'],
                "email" => $user['email'],
                "role" => $user['role'],
                'exp' => time() + (60 * 60 * 24)
            ];

            $token = $jwt->encode($payload);
            return $this->sendPayload([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ], "success", "Login successful", 200);
        } catch (PDOException $e) {
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    public function addUser($data)
    {
        try {
            $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, last_name, email, role, password_hash) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data->first_name,
                $data->last_name,
                $data->email,
                $data->role,
                $hashed_password

            ]);
            return $this->sendPayload(null, "success", "Successfully created a new record", 200);
        } catch (PDOException $e) {
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    public function addTenant($data, $files = null)
    {
        try {
            $this->pdo->beginTransaction();
            $this->ensureUploadDirectory();

            // GET UNIT ID AND CHECK FOR EXISTING ACTIVE LEASE
            $sql = "SELECT u.id, 
                    (SELECT COUNT(*) FROM leases l 
                     WHERE l.unit_id = u.id 
                     AND l.status = 'active') as has_active_lease
                    FROM units u 
                    WHERE u.unit_number = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['unit_number'] ?? $data->unit_number]);
            $unit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$unit) {
                return $this->sendPayload(null, "failed", "Unit not found.", 400);
            }

            if ($unit['has_active_lease'] > 0) {
                return $this->sendPayload(null, "failed", "This unit already has an active lease.", 400);
            }

            // CREATE LEASE
            $sql = "INSERT INTO leases (unit_id, start_date, end_date, rent_amount, status) 
                VALUES (?, ?, ?, ?, 'active')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $unit['id'],
                $data['start_date'] ?? $data->start_date,
                $data['end_date'] ?? $data->end_date,
                $data['rent_amount'] ?? $data->rent_amount
            ]);

            $leaseId = $this->pdo->lastInsertId();

            // Create deposit and payment records
            $rentAmount = $data['rent_amount'] ?? $data->rent_amount;
            $totalAmount = $data['total_amount'] ?? $data->total_amount;

            $this->createDeposit($leaseId, $rentAmount);
            $this->createPaymentRecord($leaseId, $totalAmount);

            // CREATE TENANTS
            $tenants = $data['tenants'] ?? [$data->tenants[0]];
            foreach ($tenants as $tenant) {
                $validIdPath = null;
                $validIdType = null;

                // Handle file upload if present
                if ($files && isset($files["valid_id_" . $tenant['first_name']])) {
                    $file = $files["valid_id_" . $tenant['first_name']];
                    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '_' . $tenant['first_name'] . '.' . $fileType;
                    $uploadPath = "uploads/tenant_ids/" . $fileName;

                    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        throw new Exception("Failed to upload file: " . $file['name']);
                    }
                    chmod($uploadPath, 0644);

                    $validIdPath = $uploadPath;
                    $validIdType = $fileType;
                }

                $sql = "INSERT INTO tenants (
                    first_name, last_name, lease_id, 
                    move_in_date, contact_number, email,
                    valid_id_path, valid_id_type, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $tenant['first_name'] ?? $tenant->first_name,
                    $tenant['last_name'] ?? $tenant->last_name,
                    $leaseId,
                    $data['move_in_date'] ?? $data->move_in_date,
                    $tenant['phone_number'] ?? $tenant->phone_number,
                    $tenant['email'] ?? $tenant->email,
                    $validIdPath,
                    $validIdType
                ]);
            }

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully added tenant", 200);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    private function createPaymentRecord($leaseId, $totalAmount)
    {
        try {
            $sql = "INSERT INTO payments (lease_id, total_amount, amount_paid, payment_status) VALUES (?, ?, ?, 'paid')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$leaseId, $totalAmount, $totalAmount]);

            return true;
        } catch (Exception $e) {
            error_log("Failed to create payment record: " . $e->getMessage());
            throw new Exception("Failed to create payment record");
        }
    }

    public function renewLease($data)
    {
        try {
            $this->pdo->beginTransaction();

            // Calculate duration in months
            $startDate = new DateTime($data->start_date);
            $endDate = new DateTime($data->end_date);
            $durationInMonths = ($endDate->diff($startDate)->days + 1) / 30;

            // Update old lease
            $sql = "UPDATE leases SET status = 'inactive', date_renewed = NOW() 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->lease_id]);

            // Create new lease
            $sql = "INSERT INTO leases (unit_id, start_date, end_date, rent_amount, status, previous_lease_id) 
                    VALUES (?, ?, ?, ?, 'active', ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data->unit_id,
                $data->start_date,
                $data->end_date,
                $data->rent_amount,
                $data->lease_id
            ]);

            $newLeaseId = $this->pdo->lastInsertId();

            // Create payment record for new lease
            $this->createPaymentRecord($newLeaseId, $data->total_amount);

            // Transfer tenants to new lease
            $sql = "UPDATE tenants SET lease_id = ? WHERE lease_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$newLeaseId, $data->lease_id]);

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Lease renewed successfully", 200);

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    public function endLease($data)
    {
        try {
            $this->pdo->beginTransaction();

            // Update tenants to inactive
            $sql = "UPDATE tenants 
                    SET status = 'inactive' 
                    WHERE lease_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->lease_id]);

            // Update lease status and set terminated_at
            $sql = "UPDATE leases 
                    SET status = 'inactive',
                        date_terminated = CURRENT_TIMESTAMP
                    WHERE id = ? AND status = 'active'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->lease_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Lease not found or already terminated.");
            }

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully ended lease and updated tenant status", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    public function updateValidId($tenantId, $file)
    {
        try {
            $this->pdo->beginTransaction();

            // Ensure upload directories exist
            $this->ensureUploadDirectory();

            // Process the new file
            $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileType;
            $uploadPath = "uploads/tenant_ids/" . $fileName;

            // Get current valid_id_path to delete old file
            $sql = "SELECT valid_id_path FROM tenants WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tenantId]);
            $oldPath = $stmt->fetchColumn();

            // Delete old file if exists
            if ($oldPath && file_exists($oldPath)) {
                unlink($oldPath);
            }

            // Upload new file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception("Failed to upload file");
            }

            // Update database
            $sql = "UPDATE tenants 
                    SET valid_id_path = ?, 
                        valid_id_type = ? 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$uploadPath, $fileType, $tenantId]);

            $this->pdo->commit();
            return $this->sendPayload(
                ['valid_id_url' => 'http://localhost/aricio-bonina/api/' . $uploadPath],
                "success",
                "Successfully updated valid ID",
                200
            );
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    public function forgotPassword($data)
    {
        try {
            // Check if email exists
            $sql = "SELECT id, email, first_name FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->sendPayload(null, "failed", "Email not found", 404);
            }

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_token_hash = password_hash($reset_token, PASSWORD_DEFAULT);
            $reset_token_expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Begin transaction
            $this->pdo->beginTransaction();

            try {
                // Update user with reset token
                $sql = "UPDATE users 
                        SET reset_token_hash = ?, 
                            reset_token_expires_at = ? 
                        WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $reset_token_hash,
                    $reset_token_expires_at,
                    $user['id']
                ]);

                // Send email
                $mailer = Mailer::getInstance();
                $mailer->sendPasswordReset($user['email'], $user['first_name'], $reset_token);

                $this->pdo->commit();
                return $this->sendPayload(null, "success", "Password reset instructions sent to your email", 200);
            } catch (Exception $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    public function resetPassword($data)
    {
        try {
            if (!isset($data->token) || !isset($data->password)) {
                return $this->sendPayload(null, "failed", "Missing required fields", 400);
            }

            // Find user with valid reset token
            $sql = "SELECT id FROM users 
                    WHERE reset_token_expires_at > NOW()";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->sendPayload(null, "failed", "Invalid or expired reset token", 400);
            }

            // Verify the token matches
            $sql = "SELECT reset_token_hash FROM users WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user['id']]);
            $storedHash = $stmt->fetchColumn();

            if (!password_verify($data->token, $storedHash)) {
                return $this->sendPayload(null, "failed", "Invalid reset token", 400);
            }

            // Update password and clear reset token
            $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
            $sql = "UPDATE users 
                    SET password_hash = ?,
                        reset_token_hash = NULL,
                        reset_token_expires_at = NULL
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$password_hash, $user['id']]);

            return $this->sendPayload(null, "success", "Password successfully reset", 200);
        } catch (Exception $e) {
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    private function createDeposit($leaseId, $monthlyRent)
    {
        try {
            // Create 2 months advance deposit
            $depositAmount = $monthlyRent * 2;

            $sql = "INSERT INTO deposits (lease_id, amount) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$leaseId, $depositAmount]);

            return true;
        } catch (Exception $e) {
            error_log("Failed to create deposit record: " . $e->getMessage());
            throw new Exception("Failed to create deposit record");
        }
    }

    public function updateDeposit($data)
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "SELECT amount FROM deposits WHERE lease_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->lease_id]);
            $currentAmount = $stmt->fetchColumn();

            $newAmount = $data->action === 'deduct'
                ? $currentAmount - $data->amount
                : $currentAmount + $data->amount;

            if ($newAmount < 0) {
                throw new Exception("Deduction amount exceeds current deposit");
            }

            $sql = "UPDATE deposits 
                    SET amount = ?,
                        remarks = CONCAT(COALESCE(remarks, ''), '\n', ?)
                    WHERE lease_id = ?";

            $remark = date('Y-m-d H:i:s') . " - " .
                ($data->action === 'deduct' ? "Deducted" : "Added") .
                " ₱" . number_format($data->amount, 2);

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$newAmount, $remark, $data->lease_id]);

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Deposit updated successfully", 200);

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

}






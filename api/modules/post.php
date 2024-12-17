<?php

require_once 'global.php';

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

            // Ensure upload directories exist before processing any files
            $this->ensureUploadDirectory();

            // Convert form data to object if needed
            if (is_array($data)) {
                $formData = new stdClass();
                $formData->unit_number = $data['unit_number'];
                $formData->move_in_date = $data['move_in_date'];
                $formData->start_date = $data['start_date'];
                $formData->end_date = $data['end_date'];
                $formData->rent_amount = $data['rent_amount'];
                $formData->tenants = [];

                $tenant = new stdClass();
                $tenant->first_name = $data['tenants'][0]['first_name'];
                $tenant->last_name = $data['tenants'][0]['last_name'];
                $tenant->phone_number = $data['tenants'][0]['phone_number'];
                $tenant->email = $data['tenants'][0]['email'];
                $formData->tenants[] = $tenant;

                $data = $formData;
            }

            // GET UNIT ID AND CHECK FOR EXISTING ACTIVE LEASE
            $sql = "SELECT u.id, 
                    (SELECT COUNT(*) FROM leases l 
                     WHERE l.unit_id = u.id 
                     AND l.status = 'active') as has_active_lease
                    FROM units u 
                    WHERE u.unit_number = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->unit_number]);
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
                $data->start_date,
                $data->end_date,
                $data->rent_amount
            ]);

            $leaseId = $this->pdo->lastInsertId();

            // CREATE TENANTS
            foreach ($data->tenants as $tenant) {
                $validIdPath = null;
                $validIdType = null;

                // Handle file upload if files are present
                if ($files && isset($files["valid_id_" . $tenant->first_name])) {
                    $file = $files["valid_id_" . $tenant->first_name];
                    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '_' . $tenant->first_name . '.' . $fileType;
                    $uploadPath = "uploads/tenant_ids/" . $fileName;

                    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        throw new Exception("Failed to upload file: " . $file['name']);
                    }

                    // Set proper permissions on the uploaded file
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
                    $tenant->first_name,
                    $tenant->last_name,
                    $leaseId,
                    $data->move_in_date,
                    $tenant->phone_number,
                    $tenant->email,
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


    public function renewLease($data)
    {
        try {
            $this->pdo->beginTransaction();

            // UPDATE EXISTING LEASE
            $sql = "UPDATE leases SET date_renewed = CURRENT_TIMESTAMP, status = 'inactive' WHERE id = ? AND status = 'active'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->lease_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Lease not found or already renewed.");
            }

            $sql = "INSERT INTO leases (unit_id, start_date, end_date, rent_amount, previous_lease_id, status) 
                    SELECT unit_id, ?, ?, ?, id, 'active' 
                    FROM leases 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data->start_date,
                $data->end_date,
                $data->rent_amount,
                $data->lease_id
            ]);

            $newLeaseId = $this->pdo->lastInsertId();

            $sql = "UPDATE tenants SET lease_id = ? WHERE lease_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$newLeaseId, $data->lease_id]);

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully renewed lease", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
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

}






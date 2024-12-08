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

    public function addTenant($data)
    {
        try {
            $this->pdo->beginTransaction();

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
                $sql = "INSERT INTO tenants (first_name, last_name, lease_id, move_in_date, contact_number, email) 
                    VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $tenant->first_name,
                    $tenant->last_name,
                    $leaseId,
                    $data->move_in_date,
                    $tenant->phone_number,
                    $tenant->email
                ]);
            }

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully created lease with tenants", 200);
        } catch (PDOException $e) {
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

            // Update tenants to inactive instead of deleting
            $sql = "UPDATE tenants 
                    SET status = 'inactive' 
                    WHERE lease_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->lease_id]);

            // Update lease status
            $sql = "UPDATE leases 
                    SET status = 'inactive',
                        end_date = CURRENT_DATE
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
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

}






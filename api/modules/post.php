<?php

require_once 'global.php';

class Post extends GlobalMethods
{

    private $pdo;

    public function __construct(\PDO $pdo)
    {
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
            $jwt = new Jwt("test-env-key101932");
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
            $sql = "INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data->first_name,
                $data->last_name,
                $data->email,
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
            // NOTE TO SELF: IN THE FUTURE, REPLACE unit_number WITH unit_id
            $sql = "INSERT INTO tenants (first_name, last_name, unit_id, move_in_date) 
                VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data->first_name,
                $data->last_name,
                $data->unit_number,
                $data->move_in_date
            ]);

            // GET LAST INSERTED ID 
            $tenantId = $this->pdo->lastInsertId();

            $sql = "INSERT INTO leases (tenant_id, unit_id, start_date, end_date, rent_amount) 
                VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $tenantId,
                $data->unit_number,
                $data->start_date,
                $data->end_date,
                $data->rent_amount
            ]);

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully created a new record", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }


}






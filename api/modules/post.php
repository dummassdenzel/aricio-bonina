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

    // public function registerUser($data)
    // {
    //     $this->pdo->beginTransaction();
    //     try {
    //         $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
    //         $activation_token = bin2hex(random_bytes(16));
    //         $activation_token_hash = hash("sha256", $activation_token);
    //         $sql = "INSERT INTO user (firstName, lastName, email, password, role, account_activation_hash) VALUES (?, ?, ?, ?, ?, ?)";
    //         $stmt = $this->pdo->prepare($sql);
    //         $stmt->execute([
    //             $data->firstName,
    //             $data->lastName,
    //             $data->email,
    //             $hashed_password,
    //             $data->role,
    //             $activation_token_hash
    //         ]);


    //         $this->registerRoleSpecificData($data);

    //         $this->pdo->commit();
    //         require __DIR__ . "../../src/Mailer.php";
    //         $mail = initializeMailer();

    //         $mail->setFrom("GCPractiPro@gcpractipro.online", "GCPractiProAdmin");
    //         $mail->addAddress($data->email);
    //         $mail->Subject = "Account Activation";
    //         $mail->Body = <<<END
    //         Click <a href="http://localhost:4200/activateaccount?token=$activation_token">here</a> to activate your account.

    //         END;

    //         try {
    //             $mail->send();
    //             return $this->sendPayload(null, "success", "Successfully sent activation email", 200);
    //         } catch (Exception $e) {
    //             $this->pdo->rollBack();
    //             $code = 400;
    //             return $this->sendPayload(null, "failed", $mail->ErrorInfo, $code);
    //         }
    //     } catch (PDOException $e) {
    //         $this->pdo->rollBack();
    //         return $this->handleException($e);
    //     }
    // }

    // private function registerRoleSpecificData($data)
    // {
    //     switch ($data->role) {
    //         case 'student':
    //             $this->registerStudent($data);
    //             break;
    //         case 'advisor':
    //             $this->registerAdvisor($data);
    //             break;
    //         case 'supervisor':
    //             $this->registerSupervisor($data);
    //             break;
    //         case 'admin':
    //             break;
    //         default:
    //             throw new Exception("Unknown role: " . $data->role);
    //     }
    // }

    // private function registerStudent($data)
    // {
    //     $sql = "UPDATE students SET studentId = ?, program = ?, year = ? WHERE email = ?";
    //     $stmt = $this->pdo->prepare($sql);
    //     try {
    //         $stmt->execute([
    //             $data->studentId,
    //             $data->program,
    //             $data->year,
    //             $data->email
    //         ]);
    //     } catch (PDOException $e) {
    //         if ($e->getCode() == '23000') {
    //             throw new PDOException("The student ID is already in use.", 23002);
    //         } else {
    //             throw $e;
    //         }
    //     }
    // }

    // private function registerAdvisor($data)
    // {
    //     $sql = "UPDATE coordinators SET department = ? WHERE email = ?";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute([
    //         $data->department,
    //         $data->email
    //     ]);
    // }

    // private function registerSupervisor($data)
    // {
    //     // Check if the company already exists
    //     $sql = "SELECT id FROM industry_partners WHERE company_name = ?";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute([$data->company_name]);
    //     $company = $stmt->fetch(PDO::FETCH_ASSOC);

    //     if (!$company) {
    //         // If company doesn't exist, insert new company record
    //         $sql = "INSERT INTO industry_partners (company_name, address) VALUES (?, ?)";
    //         $stmt = $this->pdo->prepare($sql);
    //         $stmt->execute([
    //             $data->company_name,
    //             $data->address
    //         ]);
    //         $company_id = $this->pdo->lastInsertId();
    //     } else {
    //         // If company exists, use its ID
    //         $company_id = $company['id'];
    //     }

    //     // Update supervisor table with company_id
    //     $sql = "UPDATE supervisors SET company_id = ?, position = ?, phone = ? WHERE email = ?";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute([
    //         $company_id,
    //         $data->position,
    //         $data->phone,
    //         $data->email
    //     ]);
    // }

    // private function handleException(PDOException $e)
    // {
    //     if ($e->getCode() == '23002') {
    //         $errmsg = "The student ID is already in use.";
    //         $code = 409; // Conflict HTTP status code
    //     } elseif ($e->getCode() == '23000' && strpos($e->getMessage(), 'user.email') !== false) {
    //         $errmsg = "A user with this email already exists.";
    //         $code = 409; // Conflict HTTP status code
    //     } else {
    //         $errmsg = $e->getMessage();
    //         $code = 400; // Bad request HTTP status code
    //     }
    //     return $this->sendPayload(null, "failed", $errmsg, $code);
    // }


}






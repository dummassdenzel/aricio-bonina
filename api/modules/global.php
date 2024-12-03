<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';

class GlobalMethods
{
    private $encryption_key;

    public function __construct()
    {
        try {
            $this->encryption_key = Environment::getInstance()->get('ENCRYPTION_KEY');
        } catch (Exception $e) {
            error_log("Error in constructor: " . $e->getMessage());
            throw $e;
        }
    }
    public function sendPayload($data, $remarks, $message, $code)
    {
        $status = array("remarks" => $remarks, "message" => $message);
        http_response_code($code);
        return array(
            "status" => $status,
            "payload" => $data,
            "prepared_by" => "Denzel Manz Perez",
            "timestamp" => date_create()
        );
    }

    // SEND PAYLOAD 
    // protected function sendPayload($payload, $remarks, $message, $code)
    // {
    //     $status = array(
    //         "remarks" => $remarks,
    //         "message" => $message
    //     );

    //     // ONLY ENCRYPT SUCCESSFUL RESPONSES
    //     $finalPayload = ($code === 200 && $payload !== null)
    //         ? $this->encryptPayload($payload)
    //         : $payload;

    //     $responseData = [
    //         "status" => $status,
    //         "payload" => $finalPayload,
    //         "prepared_by" => "Denzel Manz Perez",
    //         "timestamp" => date_create()
    //     ];

    //     http_response_code($code);
    //     return $responseData;
    // }

    protected function encryptPayload($data)
    {
        try {
            // CONVERT PAYLOAD TO JSON
            $jsonData = json_encode($data);

            // NOTE TO SELF: REVISE THIS
            $key = hex2bin($this->encryption_key);

            // Generate random IV
            $iv = openssl_random_pseudo_bytes(16);

            // ENCRYPT
            $encrypted = openssl_encrypt(
                $jsonData,
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            // Combine IV and encrypted data
            $combined = $iv . $encrypted;

            // ENCODE RESULT TO BASE64
            $base64 = base64_encode($combined);

            return $base64;
        } catch (Exception $e) {
            error_log("Encryption error: " . $e->getMessage());
            throw new Exception("Encryption failed");
        }
    }
}
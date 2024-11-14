<?php

require_once __DIR__ . "/../vendor/autoload.php";

class Jwt
{
    public function __construct(private string $key)
    {

    }

    /**
     * Generate a JWT token
     * @param array $payload The data to encode
     * @return string The JWT token
     */
    public function encode(array $payload): string
    {
        return \Firebase\JWT\JWT::encode(
            $payload,
            $this->key,
            'HS256'
        );
    }

    /**
     * Decode a JWT token
     * @param string $token The JWT token to decode
     * @return object The decoded data
     */
    public function decode(string $token): object
    {
        try {
            return \Firebase\JWT\JWT::decode(
                $token,
                new \Firebase\JWT\Key($this->key, 'HS256')
            );
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }
}

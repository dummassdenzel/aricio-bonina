<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static $instance = null;
    private $mailer;

    private function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    public static function getInstance(): Mailer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function setupMailer()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = Environment::getInstance()->get('GMAIL_USER');
            $this->mailer->Password = Environment::getInstance()->get('GMAIL_APP_PASS');
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;
            $this->mailer->setFrom('noreply@ariciobonina.com', 'Aricio Bonina Real Estate');

            // Set UTF-8 encoding
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';
        } catch (Exception $e) {
            error_log("Mailer setup failed: " . $e->getMessage());
            throw new Exception("Failed to setup mailer");
        }
    }

    public function sendPasswordReset(string $email, string $firstName, string $resetToken): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $firstName);
            $this->mailer->isHTML(true);

            $resetLink = "http://localhost:5173/reset-password?token=" . $resetToken;

            $this->mailer->Subject = 'Password Reset Request';
            $this->mailer->Body = $this->getPasswordResetTemplate($firstName, $resetLink);
            $this->mailer->AltBody = "Hi {$firstName},\n\n" .
                "Click this link to reset your password: {$resetLink}\n\n" .
                "This link will expire in 1 hour.\n\n" .
                "If you didn't request this, please ignore this email.";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            throw new Exception("Failed to send password reset email");
        }
    }

    private function getPasswordResetTemplate(string $firstName, string $resetLink): string
    {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #314A60;'>Password Reset Request</h2>
                <p>Hi {$firstName},</p>
                <p>You recently requested to reset your password. Click the button below to reset it:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' 
                       style='background: #14B8A6; color: white; padding: 12px 24px; 
                              text-decoration: none; border-radius: 4px; display: inline-block;'>
                        Reset Password
                    </a>
                </p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <p style='margin-top: 30px; padding-top: 30px; border-top: 1px solid #eee;'>
                    Best regards,<br>
                    Aricio Bonina Real Estate
                </p>
            </div>
        ";
    }
}
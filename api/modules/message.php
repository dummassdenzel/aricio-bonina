<?php

require_once 'global.php';

class Message extends GlobalMethods
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function sendMessage($data)
    {
        try {
            $sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->sender_id, $data->receiver_id, $data->content]);
            return $this->sendPayload(null, "success", "Message sent successfully", 200);
        } catch (PDOException $e) {
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }

    public function getMessages($userId)
    {
        try {
            $sql = "SELECT * FROM messages WHERE receiver_id = ? ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->sendPayload($messages, "success", "Messages retrieved successfully", 200);
        } catch (PDOException $e) {
            return $this->sendPayload(null, "failed", $e->getMessage(), 400);
        }
    }
}
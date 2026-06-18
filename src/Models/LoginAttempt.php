<?php
declare(strict_types=1);

namespace RyTM\Models;

use RyTM\Core\Database;

class LoginAttempt
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAttempts(string $ip, string $email): array
    {
        $stmt = $this->db->prepare("SELECT attempts, blocked_until FROM login_attempts WHERE ip = ? AND email = ?");
        $stmt->bind_param("ss", $ip, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?? ['attempts' => 0, 'blocked_until' => null];
    }

    public function incrementAttempts(string $ip, string $email): void
    {
        $stmt = $this->db->prepare("INSERT INTO login_attempts (ip, email, attempts, last_attempt) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
        $stmt->bind_param("ss", $ip, $email);
        $stmt->execute();
    }

    public function block(string $ip, string $email, int $duration): void
    {
        $blockedUntil = date('Y-m-d H:i:s', time() + $duration);
        $stmt = $this->db->prepare("UPDATE login_attempts SET blocked_until = ? WHERE ip = ? AND email = ?");
        $stmt->bind_param("sss", $blockedUntil, $ip, $email);
        $stmt->execute();
    }

    public function resetAttempts(string $ip, string $email): void
    {
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE ip = ? AND email = ?");
        $stmt->bind_param("ss", $ip, $email);
        $stmt->execute();
    }
}
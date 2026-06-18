<?php
declare(strict_types=1);

namespace RyTM\Models;

use RyTM\Core\Database;

class UserLog
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function log(?int $userId, string $action, ?string $details = null): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt = $this->db->prepare("INSERT INTO user_logs (user_id, action, details, ip, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $action, $details, $ip, $ua);
        $stmt->execute();
        $stmt->close();
    }
}
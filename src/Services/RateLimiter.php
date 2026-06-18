<?php
declare(strict_types=1);

namespace RyTM\Services;

use RyTM\Core\Database;

class RateLimiter
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Проверка лимита запросов для IP
     * @param string $ip
     * @param int $limit максимум запросов
     * @param int $window секунд
     * @return bool true если лимит не превышен
     */
    public function check(string $ip, int $limit, int $window): bool
    {
        // Создаём таблицу, если её нет
        $this->createTableIfNotExists();

        $now = time();
        $windowStart = $now - $window;

        // Удаляем старые записи
        $stmt = $this->db->prepare("DELETE FROM rate_limits WHERE last_request < ?");
        $stmt->bind_param("i", $windowStart);
        $stmt->execute();

        // Считаем запросы за последний window
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM rate_limits WHERE ip = ? AND last_request > ?");
        $stmt->bind_param("si", $ip, $windowStart);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = (int)($row['cnt'] ?? 0);

        if ($count >= $limit) {
            return false;
        }

        // Добавляем текущий запрос
        $stmt = $this->db->prepare("INSERT INTO rate_limits (ip, last_request) VALUES (?, ?)");
        $stmt->bind_param("si", $ip, $now);
        $stmt->execute();

        return true;
    }

    private function createTableIfNotExists(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip VARCHAR(45) NOT NULL,
                last_request INT NOT NULL,
                INDEX idx_ip (ip),
                INDEX idx_last_request (last_request)
            )
        ");
    }
}
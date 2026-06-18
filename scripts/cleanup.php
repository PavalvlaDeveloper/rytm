#!/usr/bin/env php
<?php
// Скрипт для очистки старых логов (запускать по крону раз в день)
// Путь: /scripts/cleanup.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use RyTM\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../config/db.php';

$db = Database::getConnection();
$days = 30; // хранить логи за 30 дней
$timestamp = time() - $days * 86400;

// Определяем для каждой таблицы поле даты и тип (datetime или timestamp)
$tables = [
    'user_logs'       => ['field' => 'created_at', 'type' => 'datetime'],
    'login_attempts'  => ['field' => 'last_attempt', 'type' => 'datetime'],
    'rate_limits'     => ['field' => 'last_request', 'type' => 'int'],
];

foreach ($tables as $table => $config) {
    $check = $db->query("SHOW TABLES LIKE '$table'");
    if ($check->num_rows === 0) {
        continue;
    }

    $field = $config['field'];
    $type = $config['type'];

    if ($type === 'datetime') {
        $stmt = $db->prepare("DELETE FROM $table WHERE $field < FROM_UNIXTIME(?)");
        $stmt->bind_param("i", $timestamp);
    } else { // int
        $stmt = $db->prepare("DELETE FROM $table WHERE $field < ?");
        $stmt->bind_param("i", $timestamp);
    }
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    echo "Deleted $deleted old records from $table\n";
}
echo "Cleanup completed.\n";
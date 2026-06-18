<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use RyTM\Core\ErrorHandler;
use RyTM\Services\RateLimiter;

// Загрузка .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$env = $_ENV['ENVIRONMENT'] ?? 'development';

// Настройка отображения ошибок
if ($env === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    // HSTS
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;");

// Подключение конфигов
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail.php';

// Регистрация обработчика ошибок
ErrorHandler::register();

// Глобальный rate limiter (100 запросов в минуту с одного IP)
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$limiter = new RateLimiter();
if (!$limiter->check($ip, 100, 60)) {
    http_response_code(429);
    die('Слишком много запросов. Пожалуйста, подождите.');
}

// Запуск приложения
use RyTM\Core\App;
App::run();
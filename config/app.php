<?php
define('APP_NAME', 'RYTM');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'development');
define('MAX_LOGIN_ATTEMPTS', (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5));
define('BLOCK_DURATION', (int)($_ENV['BLOCK_DURATION'] ?? 300));
define('MAX_POST_SIZE', 8 * 1024 * 1024);
define('ASSET_VERSION', '1.0.0');
define('LOG_RETENTION_DAYS', 30);
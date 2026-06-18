<?php
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'rytm_db');
define('DB_PORT', (int)($_ENV['DB_PORT'] ?? 3306));
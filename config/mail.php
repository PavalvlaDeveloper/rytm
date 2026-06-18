<?php
define('SMTP_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.mail.ru');
define('SMTP_PORT', (int)($_ENV['MAIL_PORT'] ?? 465));
define('SMTP_USER', $_ENV['MAIL_USERNAME'] ?? '');
define('SMTP_PASS', $_ENV['MAIL_PASSWORD'] ?? '');
define('SMTP_SECURE', 'ssl');
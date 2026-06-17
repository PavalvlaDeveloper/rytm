<?php
define('SMTP_HOST', getenv('MAIL_HOST') ?: 'smtp.mail.ru');
define('SMTP_PORT', getenv('MAIL_PORT') ?: 465);
define('SMTP_USER', getenv('MAIL_USERNAME') ?: '');
define('SMTP_PASS', getenv('MAIL_PASSWORD') ?: '');
define('SMTP_SECURE', 'ssl');
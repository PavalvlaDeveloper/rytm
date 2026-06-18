<?php
declare(strict_types=1);

namespace RyTM\Core;

class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $log = sprintf("[%s] Error: %s in %s on line %d\n", date('Y-m-d H:i:s'), $errstr, $errfile, $errline);
        error_log($log, 3, __DIR__ . '/../../logs/error.log');
        self::sendAlertEmail($log);
        return true;
    }

    public static function handleException(\Throwable $e): void
    {
        $log = sprintf(
            "[%s] Exception: %s in %s on line %d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        error_log($log, 3, __DIR__ . '/../../logs/error.log');
        self::sendAlertEmail($log);

        if (ENVIRONMENT === 'production') {
            http_response_code(500);
            echo 'Что-то пошло не так. Пожалуйста, попробуйте позже.';
        } else {
            throw $e;
        }
        exit;
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $log = sprintf("[%s] Fatal: %s in %s on line %d\n", date('Y-m-d H:i:s'), $error['message'], $error['file'], $error['line']);
            error_log($log, 3, __DIR__ . '/../../logs/error.log');
            self::sendAlertEmail($log);
            if (ENVIRONMENT === 'production') {
                http_response_code(500);
                echo 'Критическая ошибка сервера.';
            }
        }
    }

    /**
     * Отправка уведомления об ошибке на почту администратора (только в production)
     */
    private static function sendAlertEmail(string $message): void
    {
        if (ENVIRONMENT !== 'production') {
            return;
        }
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? null;
        if (!$adminEmail) {
            return;
        }
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            $mail->setFrom(SMTP_USER, 'RYTM Error Alert');
            $mail->addAddress($adminEmail);
            $mail->Subject = 'RYTM Critical Error';
            $mail->Body    = $message;
            $mail->send();
        } catch (\Exception $e) {
            // Не пишем в лог, чтобы избежать бесконечного цикла
        }
    }
}
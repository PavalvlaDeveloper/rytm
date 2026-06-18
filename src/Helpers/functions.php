<?php
declare(strict_types=1);

namespace RyTM\Helpers;

class Functions
{
    public static function redirect(string $path): void
    {
        header("Location: $path");
        exit;
    }

    public static function old(string $key): mixed
    {
        return $_SESSION['old'][$key] ?? '';
    }

    public static function addOldValue(string $key, mixed $value): void
    {
        $_SESSION['old'][$key] = $value;
    }

    public static function setMessage(string $key, string $msg): void
    {
        $_SESSION['message'][$key] = $msg;
    }

    public static function getMessage(string $key): string
    {
        $msg = $_SESSION['message'][$key] ?? '';
        unset($_SESSION['message'][$key]);
        return $msg;
    }

    public static function hasMessage(string $key): bool
    {
        return isset($_SESSION['message'][$key]);
    }

    public static function addValidationError(string $field, string $msg): void
    {
        $_SESSION['validation'][$field] = $msg;
    }

    public static function hasValidationError(string $field): bool
    {
        return isset($_SESSION['validation'][$field]);
    }

    public static function validationErrorMessage(string $field): string
    {
        $msg = $_SESSION['validation'][$field] ?? '';
        unset($_SESSION['validation'][$field]);
        return $msg;
    }

    public static function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function maskEmail(string $email): string
    {
        $atPos = strpos($email, '@');
        if ($atPos === false) return $email;
        $local = substr($email, 0, $atPos);
        $domain = substr($email, $atPos + 1);
        if (strlen($local) > 2) {
            return $local[0] . str_repeat('*', strlen($local)-2) . substr($local, -1) . '@' . $domain;
        }
        return $local[0] . str_repeat('*', strlen($local)-1) . '@' . $domain;
    }

    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function validateEmailDomain(string $email): bool
    {
        $domain = substr(strrchr($email, "@"), 1);
        if (empty($domain)) return false;
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }

    /**
     * Санизация строковых данных (удаление тегов, обрезка пробелов)
     */
    public static function sanitize(string $input): string
    {
        return trim(strip_tags($input));
    }
}
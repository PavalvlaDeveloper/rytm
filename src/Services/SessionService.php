<?php
declare(strict_types=1);

namespace RyTM\Services;

class SessionService
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $cookieParams = [
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict'
            ];
            session_set_cookie_params($cookieParams);
            session_start();
        }

        // Защита от фиксации сессии – проверка IP и User-Agent
        if ($this->has('user')) {
            $storedIp = $this->get('_ip') ?? '';
            $storedUa = $this->get('_ua') ?? '';
            $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';

            if ($storedIp !== $currentIp || $storedUa !== $currentUa) {
                $this->destroy();
                header('Location: /login');
                exit;
            }
        }
    }

    public function set($key, $value): void
    {
        $_SESSION[$key] = $value;
        if ($key === 'user') {
            $_SESSION['_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
    }

    public function get($key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function has($key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove($key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return $this->has('user');
    }
}
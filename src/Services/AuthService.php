<?php
declare(strict_types=1);

namespace RyTM\Services;

use RyTM\Models\User;
use RyTM\Models\LoginAttempt;
use RyTM\Models\UserLog;

class AuthService
{
    private User $userModel;
    private MailService $mailService;
    private SessionService $session;
    private LoginAttempt $loginAttempt;
    private UserLog $userLog;

    public function __construct()
    {
        $this->userModel = new User();
        $this->mailService = new MailService();
        $this->session = new SessionService();
        $this->loginAttempt = new LoginAttempt();
        $this->userLog = new UserLog();
    }

    public function initiateRegistration(string $username, string $email, string $password): array
    {
        if ($this->userModel->findByEmail($email)) {
            return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
        }
        if ($this->userModel->findByUsername($username)) {
            return ['success' => false, 'message' => 'Пользователь с таким логином уже существует'];
        }

        // Используем Argon2id для хеширования пароля
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 1 << 17, // 128 MB
            'time_cost'   => 4,
            'threads'     => 2,
        ]);
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->session->set('registration_data', [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'code' => $code,
            'expires_at' => time() + 900
        ]);

        $this->userLog->log(null, 'register_attempt', "Email: $email, Username: $username");

        $sent = $this->mailService->sendConfirmationEmail($email, $code);
        if (!$sent) {
            $this->userLog->log(null, 'register_failed', "Email: $email, Причина: не удалось отправить письмо");
            return ['success' => false, 'message' => 'Не удалось отправить письмо. Попробуйте позже.'];
        }
        return ['success' => true];
    }

    public function confirmRegistration(string $code): array
    {
        $data = $this->session->get('registration_data');
        if (!$data) {
            $this->userLog->log(null, 'confirm_failed', 'Данные регистрации не найдены в сессии');
            return ['success' => false, 'message' => 'Данные регистрации не найдены'];
        }
        if (time() > $data['expires_at']) {
            $this->session->remove('registration_data');
            $this->userLog->log(null, 'confirm_failed', "Email: {$data['email']}, Причина: истёк срок действия кода");
            return ['success' => false, 'message' => 'Срок действия кода истёк. Зарегистрируйтесь заново.'];
        }
        if (!hash_equals($data['code'], $code)) {
            $this->userLog->log(null, 'confirm_failed', "Email: {$data['email']}, Причина: неверный код");
            return ['success' => false, 'message' => 'Неверный код подтверждения'];
        }

        $userId = $this->userModel->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => 'buyer'
        ]);

        if (!$userId) {
            $this->userLog->log(null, 'register_failed', "Email: {$data['email']}, Причина: ошибка сохранения пользователя");
            return ['success' => false, 'message' => 'Ошибка сохранения пользователя'];
        }

        $user = $this->userModel->findByEmail($data['email']);
        $this->session->remove('registration_data');

        $this->userLog->log($userId, 'register_success', "Email: {$data['email']}, Username: {$data['username']}");

        return ['success' => true, 'user' => $user];
    }

    public function login(string $usernameOrEmail, string $password)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $blocked = $this->checkLoginBlock($ip, $usernameOrEmail);
        if ($blocked !== null) {
            sleep(1);
            return false;
        }

        $user = $this->userModel->findByUsernameOrEmail($usernameOrEmail);
        if (!$user) {
            $this->loginAttempt->incrementAttempts($ip, $usernameOrEmail);
            $this->applyBlockIfNeeded($ip, $usernameOrEmail);
            $this->userLog->log(null, 'login_failed', "Login: $usernameOrEmail, Причина: пользователь не найден");
            return false;
        }
        // Проверка пароля (Argon2id автоматически определяется password_verify)
        if (!password_verify($password, $user['password_hash'])) {
            $this->loginAttempt->incrementAttempts($ip, $usernameOrEmail);
            $this->applyBlockIfNeeded($ip, $usernameOrEmail);
            $this->userLog->log($user['id'], 'login_failed', "Login: $usernameOrEmail, Причина: неверный пароль");
            return false;
        }

        // Успех
        $this->loginAttempt->resetAttempts($ip, $usernameOrEmail);
        $this->userModel->updateLastLogin($user['id']);
        $this->userLog->log($user['id'], 'login_success', "Login: $usernameOrEmail");
        return $user;
    }

    private function applyBlockIfNeeded(string $ip, string $email): void
    {
        $data = $this->loginAttempt->getAttempts($ip, $email);
        if ($data['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $this->loginAttempt->block($ip, $email, BLOCK_DURATION);
            $this->userLog->log(null, 'ip_blocked', "IP: $ip, Email: $email, Причина: превышение попыток входа");
        }
    }

    public function checkLoginBlock(string $ip, string $email): ?int
    {
        $data = $this->loginAttempt->getAttempts($ip, $email);
        if ($data && $data['blocked_until'] && strtotime($data['blocked_until']) > time()) {
            return (int)(strtotime($data['blocked_until']) - time());
        }
        return null;
    }

    public function resendConfirmationCode(): array
    {
        $data = $this->session->get('registration_data');
        if (!$data) {
            return ['success' => false, 'error' => 'Данные регистрации не найдены'];
        }
        $newCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $data['code'] = $newCode;
        $data['expires_at'] = time() + 900;
        $this->session->set('registration_data', $data);

        $this->userLog->log(null, 'resend_code', "Email: {$data['email']}");

        $sent = $this->mailService->sendConfirmationEmail($data['email'], $newCode);
        if (!$sent) {
            return ['success' => false, 'error' => 'Не удалось отправить письмо'];
        }
        return ['success' => true];
    }

    public function checkResendRateLimit(): bool
    {
        $attempts = $this->session->get('resend_attempts') ?: 0;
        return $attempts < 3;
    }

    public function incrementResendAttempts(): void
    {
        $attempts = $this->session->get('resend_attempts') ?: 0;
        $this->session->set('resend_attempts', $attempts + 1);
    }

    public function checkConfirmRateLimit(): bool
    {
        $attempts = $this->session->get('confirm_attempts') ?: 0;
        return $attempts < 5;
    }

    public function incrementConfirmAttempts(): void
    {
        $attempts = $this->session->get('confirm_attempts') ?: 0;
        $this->session->set('confirm_attempts', $attempts + 1);
    }

    public function resetConfirmAttempts(): void
    {
        $this->session->remove('confirm_attempts');
    }

    public function logSuspicious(string $message, string $context = ''): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        error_log("[RYTM] SUSPICIOUS: $message | Context: $context | IP: $ip | UA: $ua");
        $this->userLog->log(null, 'suspicious', "$message | Context: $context");
    }
}
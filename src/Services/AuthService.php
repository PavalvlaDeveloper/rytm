<?php
namespace RyTM\Services;

use RyTM\Models\User;
use RyTM\Helpers\Functions;

class AuthService
{
    private $userModel;
    private $mailService;
    private $session;

    public function __construct()
    {
        $this->userModel = new User();
        $this->mailService = new MailService();
        $this->session = new SessionService();
    }

    public function initiateRegistration($username, $email, $password)
    {
        if ($this->userModel->findByEmail($email)) {
            return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
        }
        if ($this->userModel->findByUsername($username)) {
            return ['success' => false, 'message' => 'Пользователь с таким логином уже существует'];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->session->set('registration_data', [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'code' => $code,
            'expires_at' => time() + 900
        ]);

        $sent = $this->mailService->sendConfirmationEmail($email, $code);
        if (!$sent) {
            return ['success' => false, 'message' => 'Не удалось отправить письмо. Попробуйте позже.'];
        }
        return ['success' => true];
    }

    public function confirmRegistration($code)
    {
        $data = $this->session->get('registration_data');
        if (!$data) {
            return ['success' => false, 'message' => 'Данные регистрации не найдены'];
        }
        if (time() > $data['expires_at']) {
            $this->session->remove('registration_data');
            return ['success' => false, 'message' => 'Срок действия кода истёк. Зарегистрируйтесь заново.'];
        }
        if ($code !== $data['code']) {
            return ['success' => false, 'message' => 'Неверный код подтверждения'];
        }

        $userId = $this->userModel->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => 'buyer'
        ]);

        if (!$userId) {
            return ['success' => false, 'message' => 'Ошибка сохранения пользователя'];
        }

        $user = $this->userModel->findByEmail($data['email']);
        $this->session->remove('registration_data');

        return ['success' => true, 'user' => $user];
    }

    public function login($usernameOrEmail, $password)
    {
        $user = $this->userModel->findByUsernameOrEmail($usernameOrEmail);
        if (!$user) {
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        // обновляем время последнего входа
        $this->userModel->updateLastLogin($user['id']);
        return $user;
    }

    public function resendConfirmationCode()
    {
        $data = $this->session->get('registration_data');
        if (!$data) {
            return ['success' => false, 'error' => 'Данные регистрации не найдены'];
        }
        $newCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $data['code'] = $newCode;
        $data['expires_at'] = time() + 900;
        $this->session->set('registration_data', $data);

        $sent = $this->mailService->sendConfirmationEmail($data['email'], $newCode);
        if (!$sent) {
            return ['success' => false, 'error' => 'Не удалось отправить письмо'];
        }
        return ['success' => true];
    }
}
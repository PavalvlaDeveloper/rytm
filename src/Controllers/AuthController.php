<?php
namespace RyTM\Controllers;

use RyTM\Core\View;
use RyTM\Services\AuthService;
use RyTM\Services\SessionService;
use RyTM\Helpers\Validator;
use RyTM\Helpers\Functions;

class AuthController
{
    private $authService;
    private $session;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->session = new SessionService();
    }

    public function showRegisterStep1()
    {
        if ($this->session->isLoggedIn()) {
            Functions::redirect('/');
        }
        View::render('auth/register_step1');
    }

    public function processRegisterStep1()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Functions::redirect('/register/step1');
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== Functions::csrf_token()) {
            Functions::setMessage('error', 'Ошибка безопасности');
            Functions::redirect('/register/step1');
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        $errors = Validator::validateRegistration($username, $email, $password, $passwordConfirm);
        if (!empty($errors)) {
            foreach ($errors as $field => $msg) {
                Functions::addValidationError($field, $msg);
            }
            Functions::addOldValue('username', $username);
            Functions::addOldValue('email', $email);
            Functions::redirect('/register/step1');
        }

        $result = $this->authService->initiateRegistration($username, $email, $password);
        if ($result['success']) {
            Functions::redirect('/register/step2');
        } else {
            Functions::setMessage('error', $result['message']);
            Functions::redirect('/register/step1');
        }
    }

    public function showRegisterStep2()
    {
        if (!$this->session->has('registration_data')) {
            Functions::redirect('/register/step1');
        }
        View::render('auth/register_step2');
    }

    public function processRegisterStep2()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Functions::redirect('/register/step2');
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== Functions::csrf_token()) {
            Functions::setMessage('error', 'Ошибка безопасности');
            Functions::redirect('/register/step2');
        }

        $code = '';
        for ($i = 1; $i <= 6; $i++) {
            $digit = $_POST['code' . $i] ?? '';
            if (!ctype_digit($digit) || strlen($digit) !== 1) {
                Functions::addValidationError('code', 'Код должен состоять из цифр');
                Functions::redirect('/register/step2');
            }
            $code .= $digit;
        }

        $result = $this->authService->confirmRegistration($code);
        if ($result['success']) {
            $user = $result['user'];
            $this->session->set('user', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => $user['avatar_url'] ?? null,
                'is_verified' => $user['is_verified']
            ]);
            Functions::redirect('/register/step3');
        } else {
            Functions::setMessage('error', $result['message']);
            Functions::redirect('/register/step2');
        }
    }

    public function showRegisterStep3()
    {
        if (!$this->session->isLoggedIn()) {
            Functions::redirect('/');
        }
        View::render('auth/register_step3');
    }

    public function showLogin()
    {
        if ($this->session->isLoggedIn()) {
            Functions::redirect('/');
        }
        View::render('auth/login');
    }

    public function processLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Functions::redirect('/login');
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== Functions::csrf_token()) {
            Functions::setMessage('error', 'Ошибка безопасности');
            Functions::redirect('/login');
        }

        $usernameOrEmail = trim($_POST['username-email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->authService->login($usernameOrEmail, $password);
        if ($user) {
            $this->session->set('user', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => $user['avatar_url'] ?? null,
                'is_verified' => $user['is_verified']
            ]);
            Functions::redirect('/');
        } else {
            Functions::setMessage('error', 'Неверные учётные данные');
            Functions::addOldValue('username-email', $usernameOrEmail);
            Functions::redirect('/login');
        }
    }

    public function logout()
    {
        $this->session->destroy();
        Functions::redirect('/login');
    }

    public function resendCode()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['csrf_token']) || $input['csrf_token'] !== Functions::csrf_token()) {
            echo json_encode(['success' => false, 'error' => 'CSRF error']);
            exit;
        }
        $result = $this->authService->resendConfirmationCode();
        echo json_encode($result);
        exit;
    }
}
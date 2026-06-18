<?php
declare(strict_types=1);

namespace RyTM\Controllers;

use RyTM\Core\View;
use RyTM\Services\AuthService;
use RyTM\Services\SessionService;
use RyTM\Helpers\Validator;
use RyTM\Helpers\Functions as F;

class AuthController
{
    private AuthService $authService;
    private SessionService $session;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->session = new SessionService();
    }

    public function showHome(): void
    {
        View::render('home');
    }

    public function showRegisterStep1(): void
    {
        if ($this->session->isLoggedIn()) {
            F::redirect('/');
        }
        View::render('auth/register_step1');
    }

    public function processRegisterStep1(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            F::redirect('/register/step1');
        }
        if (!isset($_POST['csrf_token']) || !F::verifyCsrfToken($_POST['csrf_token'])) {
            F::setMessage('error', 'Ошибка безопасности');
            F::redirect('/register/step1');
        }

        // Санизация данных
        $username = F::sanitize($_POST['username'] ?? '');
        $email = F::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        $errors = Validator::validateRegistration($username, $email, $password, $passwordConfirm);
        if (!empty($errors)) {
            foreach ($errors as $field => $msg) {
                F::addValidationError($field, $msg);
            }
            F::addOldValue('username', $username);
            F::addOldValue('email', $email);
            F::redirect('/register/step1');
        }

        $result = $this->authService->initiateRegistration($username, $email, $password);
        if ($result['success']) {
            F::redirect('/register/step2');
        } else {
            F::setMessage('error', $result['message']);
            F::redirect('/register/step1');
        }
    }

    public function showRegisterStep2(): void
    {
        if (!$this->session->has('registration_data')) {
            F::redirect('/register/step1');
        }
        View::render('auth/register_step2');
    }

    public function processRegisterStep2(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Неправильный метод']);
                exit;
            }
            F::redirect('/register/step2');
        }

        if ($isAjax) {
            $input = json_decode(file_get_contents('php://input'), true);
            $code = $input['code'] ?? '';
            $csrf = $input['csrf_token'] ?? '';
        } else {
            $code = '';
            for ($i = 1; $i <= 6; $i++) {
                $digit = $_POST['code' . $i] ?? '';
                if (!ctype_digit($digit) || strlen($digit) !== 1) {
                    F::addValidationError('code', 'Код должен состоять из цифр');
                    F::redirect('/register/step2');
                }
                $code .= $digit;
            }
            $csrf = $_POST['csrf_token'] ?? '';
        }

        if (!F::verifyCsrfToken($csrf)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Ошибка безопасности']);
                exit;
            }
            F::setMessage('error', 'Ошибка безопасности');
            F::redirect('/register/step2');
        }

        if (!$this->authService->checkConfirmRateLimit()) {
            $this->authService->logSuspicious('Превышено количество попыток ввода кода', $_SESSION['registration_data']['email'] ?? 'unknown');
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Слишком много попыток. Попробуйте позже.']);
                exit;
            }
            F::setMessage('error', 'Слишком много попыток. Попробуйте позже.');
            F::redirect('/register/step2');
        }

        if (strlen($code) !== 6 || !ctype_digit($code)) {
            $this->authService->incrementConfirmAttempts();
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Код должен состоять из 6 цифр']);
                exit;
            }
            F::addValidationError('code', 'Код должен состоять из 6 цифр');
            F::redirect('/register/step2');
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
            session_regenerate_id(true);
            $this->authService->resetConfirmAttempts();

            if ($isAjax) {
                echo json_encode(['success' => true, 'redirect' => '/register/step3']);
                exit;
            }
            F::redirect('/register/step3');
        } else {
            $this->authService->incrementConfirmAttempts();
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $result['message']]);
                exit;
            }
            F::setMessage('error', $result['message']);
            F::redirect('/register/step2');
        }
    }

    public function showRegisterStep3(): void
    {
        if (!$this->session->isLoggedIn()) {
            F::redirect('/');
        }
        View::render('auth/register_step3');
    }

    public function showLogin(): void
    {
        if ($this->session->isLoggedIn()) {
            F::redirect('/');
        }
        View::render('auth/login');
    }

    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            F::redirect('/login');
        }
        if (!isset($_POST['csrf_token']) || !F::verifyCsrfToken($_POST['csrf_token'])) {
            F::setMessage('error', 'Ошибка безопасности');
            F::redirect('/login');
        }

        $usernameOrEmail = F::sanitize($_POST['username-email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $blockedFor = $this->authService->checkLoginBlock($ip, $usernameOrEmail);
        if ($blockedFor !== null) {
            sleep(2);
            F::setMessage('error', 'Слишком много попыток. Попробуйте через ' . ceil($blockedFor / 60) . ' минут.');
            F::redirect('/login');
        }

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
            session_regenerate_id(true);
            F::redirect('/');
        } else {
            F::setMessage('error', 'Неверные учётные данные');
            F::addOldValue('username-email', $usernameOrEmail);
            F::redirect('/login');
        }
    }

    public function logout(): void
    {
        // Логируем выход
        if ($this->session->isLoggedIn()) {
            $userId = $_SESSION['user']['id'] ?? null;
            $userLog = new \RyTM\Models\UserLog();
            $userLog->log($userId, 'logout', "User logged out");
        }
        $this->session->destroy();
        F::redirect('/login');
    }

    public function resendCode(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['csrf_token']) || !F::verifyCsrfToken($input['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'CSRF error']);
            exit;
        }

        if (!$this->authService->checkResendRateLimit()) {
            $this->authService->logSuspicious('Превышен лимит повторной отправки кода', $_SESSION['registration_data']['email'] ?? 'unknown');
            echo json_encode(['success' => false, 'error' => 'Слишком много запросов. Попробуйте позже.']);
            exit;
        }

        $result = $this->authService->resendConfirmationCode();
        if ($result['success']) {
            $this->authService->incrementResendAttempts();
        }
        echo json_encode($result);
        exit;
    }

    public function uploadAvatar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            F::redirect('/register/step3');
        }
        if (!isset($_POST['csrf_token']) || !F::verifyCsrfToken($_POST['csrf_token'])) {
            F::setMessage('error', 'Ошибка безопасности');
            F::redirect('/register/step3');
        }
        if (!$this->session->isLoggedIn()) {
            F::redirect('/');
        }

        $userId = $_SESSION['user']['id'];

        if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 10 * 1024 * 1024) {
            F::setMessage('error', 'Слишком большой объём данных');
            F::redirect('/register/step3');
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            F::setMessage('error', 'Файл не выбран');
            F::redirect('/register/step3');
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxSize = 5 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            F::setMessage('error', 'Ошибка загрузки файла');
            F::redirect('/register/step3');
        }

        // Защита от Path Traversal (новое)
        if (strpos($file['name'], '..') !== false) {
            F::setMessage('error', 'Недопустимое имя файла');
            F::redirect('/register/step3');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, $allowedTypes)) {
            $this->authService->logSuspicious('Попытка загрузки недопустимого файла (MIME)', (string)$userId);
            F::setMessage('error', 'Разрешены только изображения (JPG, PNG, GIF)');
            F::redirect('/register/step3');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->authService->logSuspicious('Загружен файл, не являющийся корректным изображением', (string)$userId);
            F::setMessage('error', 'Файл не является корректным изображением');
            F::redirect('/register/step3');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            F::setMessage('error', 'Недопустимое расширение файла');
            F::redirect('/register/step3');
        }

        if (preg_match('/\.[^.]+\./', $file['name'])) {
            F::setMessage('error', 'Недопустимое имя файла');
            F::redirect('/register/step3');
        }

        if ($file['size'] > $maxSize) {
            F::setMessage('error', 'Размер файла не должен превышать 5 МБ');
            F::redirect('/register/step3');
        }

        $newName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $uploadDir = __DIR__ . '/../../storage/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $targetPath = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $dbPath = 'storage/avatars/' . $newName;
            $userModel = new \RyTM\Models\User();
            $userModel->updateAvatar($userId, $dbPath);
            $_SESSION['user']['avatar'] = $dbPath;

            $userLog = new \RyTM\Models\UserLog();
            $userLog->log($userId, 'avatar_upload', "Файл: $newName");

            F::setMessage('success', 'Аватарка загружена');
            F::redirect('/');
        } else {
            F::setMessage('error', 'Не удалось сохранить файл');
            F::redirect('/register/step3');
        }
    }
}
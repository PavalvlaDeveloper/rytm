<?php
use function RyTM\Helpers\csrf_token;
use function RyTM\Helpers\hasMessage;
use function RyTM\Helpers\getMessage;
use function RyTM\Helpers\old;
use function RyTM\Helpers\hasValidationError;
use function RyTM\Helpers\validationErrorMessage;

$title = 'Вход';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>ВХОД</h1>
        <?php if (hasMessage('error')): ?>
            <div class="error-msg"><?= htmlspecialchars(getMessage('error')) ?></div>
        <?php endif; ?>
        <form action="/login" method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="input-group <?= hasValidationError('username-email') ? 'error' : '' ?>">
                <input type="text" name="username-email" placeholder="Логин или Email" value="<?= old('username-email') ?>" required>
                <?php if (hasValidationError('username-email')): ?>
                    <span class="error-text"><?= validationErrorMessage('username-email') ?></span>
                <?php endif; ?>
            </div>
            <div class="input-group <?= hasValidationError('password') ? 'error' : '' ?>">
                <input type="password" name="password" id="login-password" placeholder="Пароль" required>
                <button type="button" class="toggle-password" data-target="login-password">👁</button>
                <?php if (hasValidationError('password')): ?>
                    <span class="error-text"><?= validationErrorMessage('password') ?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn-primary">Войти</button>
        </form>
        <div class="auth-footer">
            Нет аккаунта? <a href="/register/step1">Регистрация</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$scripts = '<script src="/assets/js/registration.js"></script>';
require __DIR__ . '/../layouts/main.php';
?>
<?php
use RyTM\Helpers\Functions as F;

$title = 'Вход / РИТМ';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>ВХОД</h1>
        <?php if (F::hasMessage('error')): ?>
            <div class="error-msg"><?= F::escape(F::getMessage('error')) ?></div>
        <?php endif; ?>
        <form action="/login" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= F::csrf_token() ?>">
            <div class="input-group <?= F::hasValidationError('username-email') ? 'error' : '' ?>">
                <input type="text" name="username-email" placeholder="Логин или Email" value="<?= F::escape(F::old('username-email')) ?>" required>
                <?php if (F::hasValidationError('username-email')): ?>
                    <span class="error-text"><?= F::escape(F::validationErrorMessage('username-email')) ?></span>
                <?php endif; ?>
            </div>
            <div class="input-group <?= F::hasValidationError('password') ? 'error' : '' ?>">
                <input type="password" name="password" id="login-password" placeholder="Пароль" required>
                <button type="button" class="toggle-password" data-target="login-password" data-show="false">👁</button>
                <?php if (F::hasValidationError('password')): ?>
                    <span class="error-text"><?= F::escape(F::validationErrorMessage('password')) ?></span>
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
<?php
use function RyTM\Helpers\csrf_token;
use function RyTM\Helpers\hasMessage;
use function RyTM\Helpers\getMessage;
use function RyTM\Helpers\old;
use function RyTM\Helpers\hasValidationError;
use function RyTM\Helpers\validationErrorMessage;

$title = 'Регистрация – шаг 1';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>РЕГИСТРАЦИЯ</h1>
        <?php if (hasMessage('error')): ?>
            <div class="error-msg"><?= htmlspecialchars(getMessage('error')) ?></div>
        <?php endif; ?>
        <form action="/register/step1" method="post" id="register-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="input-group <?= hasValidationError('username') ? 'error' : '' ?>">
                <input type="text" name="username" placeholder="Введите логин" value="<?= old('username') ?>" required>
                <?php if (hasValidationError('username')): ?>
                    <span class="error-text"><?= validationErrorMessage('username') ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group <?= hasValidationError('email') ? 'error' : '' ?>">
                <input type="email" name="email" placeholder="Введите почту" value="<?= old('email') ?>" required>
                <?php if (hasValidationError('email')): ?>
                    <span class="error-text"><?= validationErrorMessage('email') ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group <?= hasValidationError('password') ? 'error' : '' ?>">
                <input type="password" name="password" id="password" placeholder="Введите пароль" required>
                <button type="button" class="toggle-password" data-target="password">👁</button>
                <?php if (hasValidationError('password')): ?>
                    <span class="error-text"><?= validationErrorMessage('password') ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group <?= hasValidationError('password_confirmation') ? 'error' : '' ?>">
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Повторите пароль" required>
                <button type="button" class="toggle-password" data-target="password_confirmation">👁</button>
                <?php if (hasValidationError('password_confirmation')): ?>
                    <span class="error-text"><?= validationErrorMessage('password_confirmation') ?></span>
                <?php endif; ?>
            </div>

            <div class="agreement">
                <span class="hint">Регистрируясь, Вы автоматически соглашаетесь с <a href="/terms" target="_blank">правилами сайта</a></span>
            </div>

            <button type="submit" class="btn-primary">Далее</button>
        </form>
        <div class="auth-footer">
            <a href="/login">Уже есть аккаунт?</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$scripts = '<script src="/assets/js/registration.js"></script>';
require __DIR__ . '/../layouts/main.php';
?>
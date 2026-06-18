<?php
use RyTM\Helpers\Functions as F;

$title = 'Регистрация – шаг 1 / РИТМ';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>РЕГИСТРАЦИЯ</h1>
        <?php if (F::hasMessage('error')): ?>
            <div class="error-msg"><?= F::escape(F::getMessage('error')) ?></div>
        <?php endif; ?>
        <form action="/register/step1" method="post" id="register-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= F::csrf_token() ?>">
            
            <div class="input-group <?= F::hasValidationError('username') ? 'error' : '' ?>">
                <input type="text" name="username" placeholder="Введите логин" value="<?= F::escape(F::old('username')) ?>" required>
                <?php if (F::hasValidationError('username')): ?>
                    <span class="error-text"><?= F::escape(F::validationErrorMessage('username')) ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group <?= F::hasValidationError('email') ? 'error' : '' ?>">
                <input type="email" name="email" placeholder="Введите почту" value="<?= F::escape(F::old('email')) ?>" required>
                <?php if (F::hasValidationError('email')): ?>
                    <span class="error-text"><?= F::escape(F::validationErrorMessage('email')) ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group <?= F::hasValidationError('password') ? 'error' : '' ?>">
                <input type="password" name="password" id="password" placeholder="Введите пароль" required>
                <button type="button" class="toggle-password" data-target="password" data-show="false">👁</button>
                <?php if (F::hasValidationError('password')): ?>
                    <span class="error-text"><?= F::escape(F::validationErrorMessage('password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="input-group <?= F::hasValidationError('password_confirmation') ? 'error' : '' ?>">
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Повторите пароль" required>
                <button type="button" class="toggle-password" data-target="password_confirmation" data-show="false">👁</button>
                <?php if (F::hasValidationError('password_confirmation')): ?>
                    <span class="error-text"><?= F::escape(F::validationErrorMessage('password_confirmation')) ?></span>
                <?php endif; ?>
            </div>

            <div class="agreement">
                <span><a href="/terms" target="_blank">Соглашение с правилами</a></span>
                <span class="hint">Регистрируясь, Вы автоматически соглашаетесь с правилами сайта</span>
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
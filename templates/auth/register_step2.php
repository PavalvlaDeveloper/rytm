<?php
use RyTM\Helpers\Functions as F;

$title = 'Регистрация – шаг 2 / РИТМ';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>ПРОВЕРОЧНЫЙ КОД</h1>
        <p class="subtitle">
            Введите шестизначный код, который был выслан Вам на почту:<br>
            <span class="email-masked"><?= F::escape(F::maskEmail($_SESSION['registration_data']['email'] ?? '')) ?></span>
        </p>
        <?php if (F::hasMessage('error')): ?>
            <div class="error-msg"><?= F::escape(F::getMessage('error')) ?></div>
        <?php endif; ?>
        <?php if (F::hasValidationError('code')): ?>
            <div class="error-msg"><?= F::escape(F::validationErrorMessage('code')) ?></div>
        <?php endif; ?>
        <form action="/register/step2" method="post" id="confirm-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= F::csrf_token() ?>">
            <div class="code-inputs">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="text" name="code<?= $i ?>" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn-primary">Далее</button>
        </form>
        <div class="resend">
            <button id="resend-code" class="btn-link">Выслать код повторно (60 сек)</button>
        </div>
        <div class="auth-footer">
            <a href="/register/step1">Назад</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$scripts = '<script src="/assets/js/registration.js"></script>';
require __DIR__ . '/../layouts/main.php';
?>
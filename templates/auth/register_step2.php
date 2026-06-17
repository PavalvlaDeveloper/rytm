<?php
use function RyTM\Helpers\csrf_token;
use function RyTM\Helpers\hasMessage;
use function RyTM\Helpers\getMessage;
use function RyTM\Helpers\hasValidationError;
use function RyTM\Helpers\validationErrorMessage;
use function RyTM\Helpers\maskEmail;

$title = 'Регистрация – шаг 2';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>Подтверждение</h1>
        <p class="subtitle">Код отправлен на <?= htmlspecialchars(maskEmail($_SESSION['registration_data']['email'] ?? '')) ?></p>
        <?php if (hasMessage('error')): ?>
            <div class="error-msg"><?= htmlspecialchars(getMessage('error')) ?></div>
        <?php endif; ?>
        <?php if (hasValidationError('code')): ?>
            <div class="error-msg"><?= validationErrorMessage('code') ?></div>
        <?php endif; ?>
        <form action="/register/step2" method="post" id="confirm-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="code-inputs">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="text" name="code<?= $i ?>" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn-primary">Подтвердить</button>
        </form>
        <div class="resend">
            <button id="resend-code" class="btn-link">Отправить код повторно</button>
            <span id="resend-timer" style="display:none;"></span>
        </div>
        <div class="auth-footer">
            <a href="/register/step1">← Назад</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$scripts = '<script src="/assets/js/registration.js"></script>';
require __DIR__ . '/../layouts/main.php';
?>
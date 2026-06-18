<?php
use RyTM\Helpers\Functions as F;

$title = 'Регистрация – шаг 3 / РИТМ';
$username = $_SESSION['user']['username'] ?? 'Пользователь';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>Поздравляем, Вы зарегистрировались!</h1>
        <p class="username-display"><?= F::escape($username) ?></p>

        <?php if (F::hasMessage('error')): ?>
            <div class="error-msg"><?= F::escape(F::getMessage('error')) ?></div>
        <?php endif; ?>
        <?php if (F::hasValidationError('avatar')): ?>
            <div class="error-msg"><?= F::escape(F::validationErrorMessage('avatar')) ?></div>
        <?php endif; ?>

        <form action="/register/avatar" method="post" enctype="multipart/form-data" id="avatar-form">
            <input type="hidden" name="csrf_token" value="<?= F::csrf_token() ?>">
            <div class="avatar-upload">
                <div class="avatar-preview" id="avatar-preview" style="cursor:pointer;">
                    <span class="placeholder">Загрузите изображение</span>
                </div>
                <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/gif" style="display:none;">
                <button type="submit" class="btn-primary">Войти</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$scripts = '<script src="/assets/js/registration.js"></script>';
require __DIR__ . '/../layouts/main.php';
?>
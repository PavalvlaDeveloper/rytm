<?php
$title = 'Регистрация завершена';
ob_start();
?>
<div class="auth-container">
    <div class="auth-card success">
        <h1>Поздравляем!</h1>
        <p>Вы успешно зарегистрировались в системе RYTM.</p>
        <p>Теперь вы можете <a href="/login">войти</a> и начать пользоваться сервисом.</p>
        <a href="/" class="btn-primary">На главную</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
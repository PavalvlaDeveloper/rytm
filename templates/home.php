<?php
$title = 'RYTM – Главная';
ob_start();
?>
<div class="home-container">
    <h1>Добро пожаловать в RYTM!</h1>
    <p>Магазин битов и сниппетов.</p>
    <?php if (isset($_SESSION['user'])): ?>
        <p>Вы вошли как <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></p>
        <?php if ($_SESSION['user']['avatar']): ?>
            <img src="<?= htmlspecialchars($_SESSION['user']['avatar']) ?>" alt="Avatar" style="width:50px;height:50px;border-radius:50%;">
        <?php endif; ?>
        <a href="/logout" class="btn-primary">Выйти</a>
    <?php else: ?>
        <a href="/login" class="btn-primary">Войти</a>
        <a href="/register/step1" class="btn-secondary">Регистрация</a>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>
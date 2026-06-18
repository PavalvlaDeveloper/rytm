<?php
use RyTM\Helpers\Functions as F;

$title = '404 – Страница не найдена / РИТМ';
ob_start();
?>
<div class="error-container">
    <h1>404</h1>
    <p>Страница не найдена</p>
    <a href="/">На главную</a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
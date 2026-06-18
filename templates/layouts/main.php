<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \RyTM\Helpers\Functions::escape($title ?? 'RYTM') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= ASSET_VERSION ?>">
    <link rel="stylesheet" href="/assets/css/auth.css?v=<?= ASSET_VERSION ?>">
</head>
<body>
    <div class="wrapper">
        <?php if (isset($content)) echo $content; ?>
    </div>
    <script src="/assets/js/main.js?v=<?= ASSET_VERSION ?>"></script>
    <?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>
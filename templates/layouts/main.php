<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'RYTM' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="wrapper">
        <?php if (isset($content)) echo $content; ?>
    </div>
    <script src="/assets/js/main.js"></script>
    <?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>
<!DOCTYPE html>
<html lang="<?= \Core\Config::get('app.locale.language', 'pt-BR') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Página') ?> — <?= htmlspecialchars(\Core\Config::get('app.name', 'App')) ?></title>
    <link rel="icon" href="<?= \Core\Config::get('app.branding.favicon') ?>">
    <link rel="stylesheet" href="/public/css/tokens.css">
    <link rel="stylesheet" href="/public/css/reset.css">
    <link rel="stylesheet" href="/public/css/base.css">
    <link rel="stylesheet" href="/public/css/components.css">
    <link rel="stylesheet" href="/public/css/utilities.css">
</head>
<body>
    <?php require __DIR__ . '/../partials/nav.php'; ?>

    <main class="main-content">
        <?php if ($flash = \Core\Session::getFlash('success')): ?>
            <div class="alert alert--success"><?= htmlspecialchars((string) $flash) ?></div>
        <?php endif; ?>

        <?php if ($flash = \Core\Session::getFlash('error')): ?>
            <div class="alert alert--error"><?= htmlspecialchars((string) $flash) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

    <script type="module" src="/public/js/main.js"></script>
</body>
</html>

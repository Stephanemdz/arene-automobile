<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>

    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/reset.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/layout.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/components.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/typography.css">

    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL ?>/public/css/<?= e($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?= APP_URL ?>/index.php" class="logo">
            <span class="logo-icon">🏁</span>
            <span class="logo-text"><?= APP_NAME ?></span>
        </a>

        <nav class="main-nav" aria-label="Navigation principale">
            <a href="<?= APP_URL ?>/index.php">Accueil</a>
            <a href="<?= APP_URL ?>/views/events/map.php">Carte</a>

            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/views/events/submit.php">Proposer un événement</a>
                <?php if (isAdmin()): ?>
                    <a href="<?= APP_URL ?>/views/admin/events.php" class="nav-badge">Back-office</a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/src/controllers/AuthController.php?action=logout">
                    Déconnexion (<?= e($_SESSION['username']) ?>)
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/auth/login.php">Connexion</a>
                <a href="<?= APP_URL ?>/auth/register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="main-content">
    <div class="container">

        <?php
        $flash = getFlash();
        if ($flash):
        ?>
            <div class="alert alert--<?= e($flash['type']) ?>" role="alert">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

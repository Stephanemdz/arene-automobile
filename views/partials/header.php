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
    <a href="<?= APP_URL ?>/views/contact/index.php">Contact</a>

    <?php if (isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/views/events/submit.php">Proposer un événement</a>
        <a href="<?= APP_URL ?>/views/user/profile.php">Mon profil</a>

        <?php if (isAdmin()): ?>
            <a href="<?= APP_URL ?>/views/admin/events.php" class="nav-badge">
                Événements
            </a>
            <a href="<?= APP_URL ?>/views/admin/messages.php" class="nav-badge">
                Messages
                <?php
                require_once __DIR__ . '/../../src/models/ContactModel.php';
                $unread = (new ContactModel(getPDO()))->countUnread();
                if ($unread > 0):
                ?>
                    <span style="background:#fff;color:var(--color-red);
                                 border-radius:20px;font-size:10px;
                                 padding:1px 6px;margin-left:4px;
                                 font-weight:700">
                        <?= $unread ?>
                    </span>
                <?php endif; ?>
            </a>
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

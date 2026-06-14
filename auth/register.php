<?php
require_once __DIR__ . '/../config/bootstrap.php';

// Si l'utilisateur est déjà connecté, on le redirige
if (isLoggedIn()) { redirect('index.php'); }

$pageTitle = 'Inscription';
$extraCss  = ['form.css'];

// Récupération des erreurs et des anciennes valeurs en cas d'échec de validation
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];

// Nettoyage de la session après affichage
unset($_SESSION['form_errors'], $_SESSION['form_old']);

require_once __DIR__ . '/../views/partials/header.php';
?>

<section class="page-section">
    <div class="form-card">
        <h1 class="form-title">Inscription</h1>
        <p class="form-subtitle">Rejoignez la communauté Arène Automobile.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/src/controllers/AuthController.php" novalidate class="event-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action"     value="register">

            <div class="form-group">
                <label for="username" class="form-label">Pseudo *</label>
                <input type="text" id="username" name="username" class="form-input" required
                       value="<?= e($old['username'] ?? '') ?>" placeholder="JeanDupont">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email *</label>
                <input type="email" id="email" name="email" class="form-input" required
                       value="<?= e($old['email'] ?? '') ?>" placeholder="vous@exemple.fr">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe *</label>
                <input type="password" id="password" name="password" class="form-input" required
                       placeholder="••••••••">
            </div>

            <div class="form-group">
                <label for="confirm" class="form-label">Confirmer le mot de passe *</label>
                <input type="password" id="confirm" name="confirm" class="form-input" required
                       placeholder="••••••••">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary btn--lg">Créer mon compte</button>
            </div>
        </form>

        <p class="form-alt-link">
            Déjà inscrit ? <a href="<?= APP_URL ?>/auth/login.php">Se connecter</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
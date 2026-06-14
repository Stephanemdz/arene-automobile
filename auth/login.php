<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (isLoggedIn()) { redirect('index.php'); }

$pageTitle = 'Connexion';
$extraCss  = ['form.css'];

$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

require_once __DIR__ . '/../views/partials/header.php';
?>

<section class="page-section">
    <div class="form-card">
        <h1 class="form-title">Connexion</h1>
        <p class="form-subtitle">Accédez à votre espace Arène Automobile.</p>

        <form method="POST" action="<?= APP_URL ?>/src/controllers/AuthController.php" novalidate class="event-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action"     value="login">

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

            <div class="form-actions">
                <button type="submit" class="btn btn--primary btn--lg">Se connecter</button>
            </div>
        </form>

        <p class="form-alt-link">
            Pas encore de compte ? <a href="<?= APP_URL ?>/auth/register.php">S'inscrire</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>

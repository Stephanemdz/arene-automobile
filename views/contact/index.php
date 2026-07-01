<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$pageTitle = 'Contact';
$extraCss  = ['form.css'];

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

require_once __DIR__ . '/../partials/header.php';
?>

<section class="page-section">
    <div class="form-wrap">
        <div class="form-card">
            <h1 class="form-title">Nous contacter</h1>
            <p class="form-subtitle">
                Vous organisez un événement et souhaitez nous en parler directement ?
                Remplissez ce formulaire, nous vous répondrons dans les plus brefs délais.
            </p>

            <form method="POST" action="<?= APP_URL ?>/src/controllers/ContactController.php"
                  novalidate class="event-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action"     value="send">

                
                <div class="form-group <?= isset($errors['name']) ? 'has-error' : '' ?>">
                    <label for="name" class="form-label">Nom / Organisation *</label>
                    <input type="text" id="name" name="name" class="form-input"
                           maxlength="120" required
                           value="<?= e($old['name'] ?? '') ?>"
                           placeholder="Votre nom ou nom de l'organisation">
                    <?php if (isset($errors['name'])): ?>
                        <p class="form-error"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-input"
                           required
                           value="<?= e($old['email'] ?? '') ?>"
                           placeholder="vous@exemple.fr">
                    <?php if (isset($errors['email'])): ?>
                        <p class="form-error"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['subject']) ? 'has-error' : '' ?>">
                    <label for="subject" class="form-label">Sujet *</label>
                    <input type="text" id="subject" name="subject" class="form-input"
                           maxlength="255" required
                           value="<?= e($old['subject'] ?? '') ?>"
                           placeholder="ex. Proposition d'un partenariat événementiel">
                    <?php if (isset($errors['subject'])): ?>
                        <p class="form-error"><?= e($errors['subject']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['message']) ? 'has-error' : '' ?>">
                    <label for="message" class="form-label">Message *</label>
                    <textarea id="message" name="message" class="form-textarea"
                              rows="6" required
                              placeholder="Décrivez votre événement ou votre demande..."><?= e($old['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <p class="form-error"><?= e($errors['message']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn--primary btn--lg">
                        Envoyer le message
                    </button>
                    <a href="<?= APP_URL ?>/index.php" class="btn btn--ghost">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
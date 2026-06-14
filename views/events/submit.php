<?php

require_once __DIR__ . '/../../config/bootstrap.php';

requireLogin();   // Redirige si non connecté

$pageTitle = 'Proposer un événement';
$extraCss  = ['form.css', 'map.css'];
$extraJs   = ['geocoder.js'];

// Récupère les erreurs et les anciennes valeurs de formulaire
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

require_once __DIR__ . '/../partials/header.php';
?>

<section class="page-section">
    <div class="form-card">
        <h1 class="form-title">Proposer un événement</h1>
        <p class="form-subtitle">
            Votre événement sera examiné par notre équipe avant publication.
        </p>

        <form
            method="POST"
            action="<?= APP_URL ?>/src/controllers/EventController.php"
            enctype="multipart/form-data"
            novalidate
            class="event-form"
        >
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action"     value="submit">

            <!-- Coordonnées remplies automatiquement par le géocodeur JS -->
            <input type="hidden" name="latitude"  id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <!-- ── Titre ── -->
            <div class="form-group <?= isset($errors['title']) ? 'has-error' : '' ?>">
                <label for="title" class="form-label">Titre de l'événement *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-input"
                    maxlength="180"
                    required
                    value="<?= e($old['title'] ?? '') ?>"
                    placeholder="ex. Meeting GT France 2025 — Circuit Paul Ricard"
                >
                <?php if (isset($errors['title'])): ?>
                    <p class="form-error"><?= e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Type ── -->
            <div class="form-group <?= isset($errors['type']) ? 'has-error' : '' ?>">
                <label for="type" class="form-label">Type d'événement *</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="">— Choisir un type —</option>
                    <?php foreach (EVENT_TYPES as $key => $label): ?>
                        <option
                            value="<?= e($key) ?>"
                            <?= ($old['type'] ?? '') === $key ? 'selected' : '' ?>
                        ><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['type'])): ?>
                    <p class="form-error"><?= e($errors['type']) ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Date & Heure ── -->
            <div class="form-row">
                <div class="form-group <?= isset($errors['event_date']) ? 'has-error' : '' ?>">
                    <label for="event_date" class="form-label">Date *</label>
                    <input
                        type="date"
                        id="event_date"
                        name="event_date"
                        class="form-input"
                        min="<?= date('Y-m-d') ?>"
                        required
                        value="<?= e($old['event_date'] ?? '') ?>"
                    >
                    <?php if (isset($errors['event_date'])): ?>
                        <p class="form-error"><?= e($errors['event_date']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="event_time" class="form-label">Heure (optionnel)</label>
                    <input
                        type="time"
                        id="event_time"
                        name="event_time"
                        class="form-input"
                        value="<?= e($old['event_time'] ?? '') ?>"
                    >
                </div>
            </div>

            <!-- ── Lieu avec géocodage automatique ── -->
            <div class="form-group <?= isset($errors['location']) ? 'has-error' : '' ?>">
                <label for="location" class="form-label">Lieu / Adresse *</label>
                <input
                    type="text"
                    id="location"
                    name="location"
                    class="form-input"
                    required
                    value="<?= e($old['location'] ?? '') ?>"
                    placeholder="ex. Circuit Paul Ricard, Le Castellet, France"
                    autocomplete="off"
                >
                <p class="form-hint">Saisissez une adresse précise — la carte se positionnera automatiquement.</p>
                <?php if (isset($errors['location'])): ?>
                    <p class="form-error"><?= e($errors['location']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Aperçu carte de géolocalisation -->
            <div class="map-preview-wrapper">
                <div id="map-preview" class="map-preview" aria-label="Aperçu de la localisation"></div>
            </div>

            <!-- ── Description ── -->
            <div class="form-group <?= isset($errors['description']) ? 'has-error' : '' ?>">
                <label for="description" class="form-label">Description *</label>
                <textarea
                    id="description"
                    name="description"
                    class="form-textarea"
                    rows="6"
                    required
                    placeholder="Décrivez l'événement : programme, accès, tarifs, contacts…"
                ><?= e($old['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="form-error"><?= e($errors['description']) ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Image de couverture ── -->
            <div class="form-group">
                <label for="cover_image" class="form-label">Image de couverture (optionnel)</label>
                <input
                    type="file"
                    id="cover_image"
                    name="cover_image"
                    class="form-input-file"
                    accept="image/jpeg,image/png,image/webp"
                >
                <p class="form-hint">JPG, PNG ou WebP — 5 Mo maximum.</p>
            </div>

            <!-- ── Submit ── -->
            <div class="form-actions">
                <button type="submit" class="btn btn--primary btn--lg">
                    Soumettre l'événement
                </button>
                <a href="<?= APP_URL ?>/index.php" class="btn btn--ghost">Annuler</a>
            </div>
        </form>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script src="<?= APP_URL ?>/public/js/geocoder.js" defer></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

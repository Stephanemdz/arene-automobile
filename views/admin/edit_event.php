<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../src/models/EventModel.php';

requireLogin(adminOnly: true);

$pageTitle = 'Modifier un événement';
$extraCss  = ['form.css', 'map.css'];
$extraJs   = ['geocoder.js'];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { redirect('views/admin/events.php'); }

$eventModel = new EventModel(getPDO());
$event      = $eventModel->findById($id);

if (!$event) {
    setFlash('error', 'Événement introuvable.');
    redirect('views/admin/events.php');
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? $event;
unset($_SESSION['form_errors'], $_SESSION['form_old']);

require_once __DIR__ . '/../partials/header.php';
?>

<section class="page-section">
    <div class="form-card">
        <h1 class="form-title">Modifier l'événement</h1>
        <p class="form-subtitle">Statut actuel : <?= e(EVENT_STATUSES[$event['status']] ?? $event['status']) ?></p>

        <form method="POST" action="<?= APP_URL ?>/src/controllers/EventController.php"
              novalidate class="event-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action"     value="edit">
            <input type="hidden" name="event_id"   value="<?= (int)$event['id'] ?>">
            <input type="hidden" name="latitude"   id="latitude"  value="<?= e((string)($old['latitude']  ?? '')) ?>">
            <input type="hidden" name="longitude"  id="longitude" value="<?= e((string)($old['longitude'] ?? '')) ?>">

            <div class="form-group <?= isset($errors['title']) ? 'has-error' : '' ?>">
                <label for="title" class="form-label">Titre *</label>
                <input type="text" id="title" name="title" class="form-input" maxlength="180" required
                       value="<?= e($old['title'] ?? '') ?>">
                <?php if (isset($errors['title'])): ?><p class="form-error"><?= e($errors['title']) ?></p><?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['type']) ? 'has-error' : '' ?>">
                <label for="type" class="form-label">Type *</label>
                <select id="type" name="type" class="form-select" required>
                    <?php foreach (EVENT_TYPES as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($old['type'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['type'])): ?><p class="form-error"><?= e($errors['type']) ?></p><?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group <?= isset($errors['event_date']) ? 'has-error' : '' ?>">
                    <label for="event_date" class="form-label">Date *</label>
                    <input type="date" id="event_date" name="event_date" class="form-input" required
                           value="<?= e($old['event_date'] ?? '') ?>">
                    <?php if (isset($errors['event_date'])): ?><p class="form-error"><?= e($errors['event_date']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="event_time" class="form-label">Heure</label>
                    <input type="time" id="event_time" name="event_time" class="form-input"
                           value="<?= e(substr($old['event_time'] ?? '', 0, 5)) ?>">
                </div>
            </div>

            <div class="form-group <?= isset($errors['location']) ? 'has-error' : '' ?>">
                <label for="location" class="form-label">Lieu / Adresse *</label>
                <input type="text" id="location" name="location" class="form-input" required
                       value="<?= e($old['location'] ?? '') ?>">
                <?php if (isset($errors['location'])): ?><p class="form-error"><?= e($errors['location']) ?></p><?php endif; ?>
            </div>

            <div class="map-preview-wrapper">
                <div id="map-preview" class="map-preview <?= $event['latitude'] ? 'is-visible' : '' ?>"></div>
            </div>

            <div class="form-group <?= isset($errors['description']) ? 'has-error' : '' ?>">
                <label for="description" class="form-label">Description *</label>
                <textarea id="description" name="description" class="form-textarea" rows="6" required><?= e($old['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?><p class="form-error"><?= e($errors['description']) ?></p><?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary btn--lg">Enregistrer les modifications</button>
                <a href="<?= APP_URL ?>/views/admin/events.php" class="btn btn--ghost">Annuler</a>
            </div>
        </form>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script src="<?= APP_URL ?>/public/js/geocoder.js" defer></script>

<?php
if ($event['latitude'] && $event['longitude']) {
    echo '<script>';
    echo 'window.addEventListener("DOMContentLoaded", function() {';
    echo 'const lat = ' . json_encode((float)$event['latitude']) . ';';
    echo 'const lng = ' . json_encode((float)$event['longitude']) . ';';
    echo 'document.getElementById("map-preview").classList.add("is-visible");';
    echo 'const m = L.map("map-preview").setView([lat, lng], 13);';
    echo 'L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {attribution: "© OpenStreetMap"}).addTo(m);';
    echo 'L.marker([lat, lng]).addTo(m);';
    echo '});';
    echo '</script>';
}
?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
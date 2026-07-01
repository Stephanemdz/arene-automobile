<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../src/models/EventModel.php';
require_once __DIR__ . '/../../src/models/CommentModel.php';
require_once __DIR__ . '/../../src/models/FavoriteModel.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect('index.php');
}

$eventModel    = new EventModel(getPDO());
$commentModel  = new CommentModel(getPDO());
$favoriteModel = new FavoriteModel(getPDO());

$event    = $eventModel->findById($id);

if (!$event || $event['status'] !== 'accepte') {
    setFlash('error', 'Événement introuvable ou non disponible.');
    redirect('index.php');
}

$comments   = $commentModel->getByEvent($id);
$favCount   = $favoriteModel->countByEvent($id);
$isFavorite = isLoggedIn() ? $favoriteModel->isFavorite((int)$_SESSION['user_id'], $id) : false;

$pageTitle = $event['title'];
$extraCss  = ['map.css'];

require_once __DIR__ . '/../partials/header.php';
?>

<section class="page-section">
    <div class="detail-wrap">

        <!-- Bouton retour -->
        <a href="<?= APP_URL ?>/index.php" class="btn btn--ghost btn--sm"
           style="margin-bottom:24px;display:inline-flex">
            ← Retour
        </a>

        <!-- Image / Icône hero -->
        <div class="detail-hero-img">
            <?= e(EVENT_TYPES[$event['type']] ?? '🚗') ?>
        </div>

        <!-- En-tête -->
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            <span class="type-tag type-tag--<?= e($event['type']) ?>">
                <?= e(EVENT_TYPES[$event['type']] ?? $event['type']) ?>
            </span>
            <span style="font-size:13px;color:var(--color-chrome-dim)">
                ❤️ <?= $favCount ?> favori<?= $favCount > 1 ? 's' : '' ?>
            </span>
        </div>

        <h1 style="font-size:clamp(28px,5vw,42px);margin-bottom:16px">
            <?= e($event['title']) ?>
        </h1>

        <!-- Métadonnées -->
        <div class="detail-meta">
            <div class="detail-meta-item">
                📅 <strong><?= e(formatDateFr($event['event_date'])) ?>
                <?= $event['event_time'] ? ' à ' . e(substr($event['event_time'], 0, 5)) : '' ?>
                </strong>
            </div>
            <div class="detail-meta-item">
                📍 <strong><?= e($event['location']) ?></strong>
            </div>
            <div class="detail-meta-item">
                👤 Proposé par <strong><?= e($event['username']) ?></strong>
            </div>
        </div>

        <!-- Description -->
        <p class="detail-desc"><?= nl2br(e($event['description'])) ?></p>

        <!-- Bouton favori -->
        <?php if (isLoggedIn()): ?>
            <form method="POST"
                  action="<?= APP_URL ?>/src/controllers/FavoriteController.php"
                  style="margin-top:24px">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action"   value="toggle">
                <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                <button type="submit"
                        class="btn <?= $isFavorite ? 'btn--refuse' : 'btn--ghost' ?>">
                    <?= $isFavorite ? '❤️ Retirer des favoris' : '🤍 Ajouter aux favoris' ?>
                </button>
            </form>
        <?php else: ?>
            <p style="margin-top:24px;font-size:14px;color:var(--color-chrome-dim)">
                <a href="<?= APP_URL ?>/auth/login.php"
                   style="color:var(--color-red);font-weight:600">Connectez-vous</a>
                pour ajouter cet événement à vos favoris.
            </p>
        <?php endif; ?>

        <!-- Carte Leaflet -->
        <?php if ($event['latitude'] && $event['longitude']): ?>
            <div class="detail-map" id="detail-map"></div>
        <?php endif; ?>

        <!-- ── Section commentaires ── -->
        <div style="margin-top:48px;padding-top:32px;border-top:1px solid var(--border-color)">
            <h2 style="font-size:24px;margin-bottom:24px">
                Commentaires (<?= count($comments) ?>)
            </h2>

            <!-- Formulaire de commentaire -->
            <?php if (isLoggedIn()): ?>
                <form method="POST"
                      action="<?= APP_URL ?>/src/controllers/CommentController.php"
                      style="margin-bottom:32px">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action"     value="create">
                    <input type="hidden" name="event_id"   value="<?= (int)$event['id'] ?>">
                    <div class="form-group" style="margin-bottom:12px">
                        <textarea name="content" class="form-textarea" rows="3"
                                  maxlength="1000" required
                                  placeholder="Partagez votre avis sur cet événement..."></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary btn--sm">
                        Publier le commentaire
                    </button>
                </form>
            <?php else: ?>
                <p style="margin-bottom:32px;font-size:14px;color:var(--color-chrome-dim)">
                    <a href="<?= APP_URL ?>/auth/login.php"
                       style="color:var(--color-red);font-weight:600">Connectez-vous</a>
                    pour laisser un commentaire.
                </p>
            <?php endif; ?>

            <!-- Liste des commentaires -->
            <?php if (empty($comments)): ?>
                <div class="empty-state">
                    <p>Aucun commentaire pour le moment. Soyez le premier !</p>
                </div>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:16px">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <span class="comment-author">
                                    👤 <?= e($comment['username']) ?>
                                </span>
                                <span class="comment-date">
                                    <?= e(formatDateFr(substr($comment['created_at'], 0, 10))) ?>
                                </span>
                            </div>
                            <p class="comment-content">
                                <?= nl2br(e($comment['content'])) ?>
                            </p>
                            <!-- Supprimer : visible par l'auteur ou l'admin -->
                            <?php if (isLoggedIn() && ((int)$_SESSION['user_id'] === (int)$comment['user_id'] || isAdmin())): ?>
                                <form method="POST"
                                      action="<?= APP_URL ?>/src/controllers/CommentController.php"
                                      onsubmit="return confirm('Supprimer ce commentaire ?')"
                                      style="margin-top:8px">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action"     value="delete">
                                    <input type="hidden" name="comment_id" value="<?= (int)$comment['id'] ?>">
                                    <input type="hidden" name="event_id"   value="<?= (int)$event['id'] ?>">
                                    <button type="submit" class="btn btn--refuse btn--sm">
                                        Supprimer
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- Leaflet -->
<?php if ($event['latitude'] && $event['longitude']): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    window.addEventListener('DOMContentLoaded', function () {
        const lat = <?= json_encode((float)$event['latitude']) ?>;
        const lng = <?= json_encode((float)$event['longitude']) ?>;

        const map = L.map('detail-map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 18,
        }).addTo(map);
        L.marker([lat, lng]).addTo(map)
            .bindPopup('<strong><?= e($event['title']) ?></strong><br><?= e($event['location']) ?>')
            .openPopup();
    });
    </script>
<?php endif; ?>

<style>
.detail-wrap { max-width: 860px; margin: 0 auto; }
.detail-hero-img {
    height: 220px;
    background: var(--color-asphalt-light);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 72px;
    margin-bottom: 28px;
    border: 1px solid var(--border-color);
}
.detail-meta {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    margin: 16px 0 24px;
}
.detail-meta-item {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 14px;
    color: var(--color-chrome-dim);
}
.detail-meta-item strong { color: var(--color-chrome-bright); }
.detail-desc {
    font-size: 15px;
    line-height: 1.75;
    color: var(--color-chrome);
    max-width: none;
}
.detail-map {
    height: 320px;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    overflow: hidden;
    margin-top: 28px;
}
.comment-card {
    background: var(--color-asphalt-mid);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 16px;
}
.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.comment-author {
    font-weight: 600;
    font-size: 14px;
    color: var(--color-chrome-bright);
}
.comment-date {
    font-size: 12px;
    color: var(--color-chrome-dim);
}
.comment-content {
    font-size: 14px;
    color: var(--color-chrome);
    max-width: none;
    line-height: 1.65;
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
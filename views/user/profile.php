<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../src/models/EventModel.php';
require_once __DIR__ . '/../../src/models/FavoriteModel.php';

requireLogin();

$pageTitle     = 'Mon profil';
$extraCss      = [];

$eventModel    = new EventModel(getPDO());
$favoriteModel = new FavoriteModel(getPDO());

$userId    = (int) $_SESSION['user_id'];

// Événements soumis par l'utilisateur
$myEvents  = $eventModel->getByUser($userId);

// Favoris de l'utilisateur
$favorites = $favoriteModel->getByUser($userId);

require_once __DIR__ . '/../partials/header.php';
?>

<section class="page-section">
    <div class="container">

        <!-- En-tête profil -->
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:40px;
                    padding-bottom:32px;border-bottom:1px solid var(--border-color)">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--color-asphalt-light);
                        border:2px solid var(--border-color);display:flex;align-items:center;
                        justify-content:center;font-size:32px">
                👤
            </div>
            <div>
                <h1 style="font-size:28px;margin-bottom:4px">
                    <?= e($_SESSION['username']) ?>
                </h1>
                <span class="<?= isAdmin() ? 'badge-admin' : '' ?>"
                      style="font-size:13px;color:var(--color-chrome-dim)">
                    <?= isAdmin() ? 'Administrateur' : 'Membre' ?>
                </span>
            </div>
        </div>

        <!-- Stats rapides -->
        <div class="stats-row" style="margin-bottom:40px">
            <div class="stat-card">
                <div class="stat-card-num"><?= count($myEvents) ?></div>
                <div class="stat-card-lbl">Événements soumis</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-num">
                    <?= count(array_filter($myEvents, fn($e) => $e['status'] === 'accepte')) ?>
                </div>
                <div class="stat-card-lbl">Acceptés</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-num">
                    <?= count(array_filter($myEvents, fn($e) => $e['status'] === 'en_attente')) ?>
                </div>
                <div class="stat-card-lbl">En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-num"><?= count($favorites) ?></div>
                <div class="stat-card-lbl">Favoris</div>
            </div>
        </div>

        <!-- ── Mes événements soumis ── -->
        <div style="margin-bottom:48px">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Mes événements soumis</h2>
                    <p class="section-sub"><?= count($myEvents) ?> événement(s)</p>
                </div>
                <a href="<?= APP_URL ?>/views/events/submit.php"
                   class="btn btn--primary btn--sm">+ Proposer un événement</a>
            </div>

            <?php if (empty($myEvents)): ?>
                <div class="empty-state">
                    <p>Vous n'avez pas encore soumis d'événement.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap"
                     style="border:1px solid var(--border-color);border-radius:var(--radius-lg)">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myEvents as $event): ?>
                                <tr class="row-<?= $event['status'] === 'accepte' ? 'accept' : ($event['status'] === 'refuse' ? 'refuse' : 'pending') ?>">
                                    <td class="td-title"><?= e($event['title']) ?></td>
                                    <td>
                                        <span class="type-tag type-tag--<?= e($event['type']) ?>">
                                            <?= e(EVENT_TYPES[$event['type']] ?? $event['type']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size:13px;white-space:nowrap">
                                        <?= e(formatDateFr($event['event_date'])) ?>
                                    </td>
                                    <td style="font-size:13px;color:var(--color-chrome-dim)">
                                        <?= e($event['location']) ?>
                                    </td>
                                    <td>
                                        <span class="status-pill status-pill--<?= e($event['status']) ?>">
                                            <?= e(EVENT_STATUSES[$event['status']] ?? $event['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($event['status'] === 'accepte'): ?>
                                            <a href="<?= APP_URL ?>/views/events/show.php?id=<?= (int)$event['id'] ?>"
                                               class="btn btn--ghost btn--sm">Voir</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Mes favoris ── -->
        <div>
            <div class="section-head">
                <div>
                    <h2 class="section-title">Mes favoris</h2>
                    <p class="section-sub"><?= count($favorites) ?> événement(s)</p>
                </div>
            </div>

            <?php if (empty($favorites)): ?>
                <div class="empty-state">
                    <p>Vous n'avez pas encore ajouté d'événement en favori.</p>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach ($favorites as $event): ?>
                        <div class="event-card">
                            <div class="event-card-img">
                                <?= EVENT_TYPES[$event['type']] ?? '🚗' ?>
                            </div>
                            <div class="event-card-body">
                                <span class="type-tag type-tag--<?= e($event['type']) ?>">
                                    <?= e(EVENT_TYPES[$event['type']] ?? $event['type']) ?>
                                </span>
                                <div class="event-card-title"><?= e($event['title']) ?></div>
                                <div class="event-card-meta">
                                    <span>📅 <?= e(formatDateFr($event['event_date'])) ?></span>
                                    <span>📍 <?= e($event['location']) ?></span>
                                </div>
                                <div class="event-card-footer">
                                    <a href="<?= APP_URL ?>/views/events/show.php?id=<?= (int)$event['id'] ?>"
                                       class="btn btn--ghost btn--sm">Voir le détail →</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.stat-card {
    background: var(--color-asphalt-mid);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 16px 20px;
    flex: 1;
    min-width: 120px;
}
.stat-card-num {
    font-family: var(--font-display);
    font-size: 32px;
    font-weight: 700;
    color: var(--color-chrome-bright);
}
.stat-card-lbl {
    font-size: 11px;
    color: var(--color-chrome-dim);
    text-transform: uppercase;
    letter-spacing: .06em;
}
.stats-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.event-card {
    background: var(--color-asphalt-mid);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.event-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}
.event-card-img {
    height: 120px;
    background: var(--color-asphalt-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
}
.event-card-body { padding: 16px; display: flex; flex-direction: column; gap: 8px; }
.event-card-title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 700;
    color: var(--color-chrome-bright);
    text-transform: uppercase;
}
.event-card-meta { font-size: 12px; color: var(--color-chrome-dim); display: flex; flex-direction: column; gap: 3px; }
.event-card-footer { padding-top: 10px; border-top: 1px solid var(--border-color); }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
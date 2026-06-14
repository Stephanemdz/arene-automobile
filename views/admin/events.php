<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../src/models/EventModel.php';

requireLogin(adminOnly: true);   // Bloque les non-admins

$pageTitle = 'Back-office — Événements';
$extraCss  = ['admin.css'];

// Filtres GET (nettoyés, jamais interpolés en SQL)
$filterStatus = $_GET['status'] ?? '';
$filterType   = $_GET['type']   ?? '';

$eventModel = new EventModel(getPDO());
$events     = $eventModel->getFiltered(
    $filterStatus !== '' ? $filterStatus : null,
    $filterType   !== '' ? $filterType   : null
);

// Comptes par statut pour les badges
$counts = array_reduce($events, function (array $carry, array $event): array {
    $carry[$event['status']] = ($carry[$event['status']] ?? 0) + 1;
    return $carry;
}, []);

require_once __DIR__ . '/../partials/header.php';
?>

<section class="admin-section">

    <div class="admin-header">
        <h1 class="admin-title">Gestion des événements</h1>
        <span class="admin-count"><?= count($events) ?> événement(s) affiché(s)</span>
    </div>

    <form method="GET" class="filter-bar" aria-label="Filtres">
        <div class="filter-group">
            <label for="filter-status" class="filter-label">Statut</label>
            <select id="filter-status" name="status" class="form-select filter-select" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <?php foreach (EVENT_STATUSES as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filterStatus === $key ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="filter-type" class="filter-label">Type</label>
            <select id="filter-type" name="type" class="form-select filter-select" onchange="this.form.submit()">
                <option value="">Tous les types</option>
                <?php foreach (EVENT_TYPES as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filterType === $key ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <a href="<?= APP_URL ?>/views/admin/events.php" class="btn btn--ghost btn--sm">Réinitialiser</a>
    </form>

    <div class="status-summary">
        <span class="status-badge status-badge--en_attente">
            En attente : <?= $counts['en_attente'] ?? 0 ?>
        </span>
        <span class="status-badge status-badge--accepte">
            Acceptés : <?= $counts['accepte'] ?? 0 ?>
        </span>
        <span class="status-badge status-badge--refuse">
            Refusés : <?= $counts['refuse'] ?? 0 ?>
        </span>
    </div>

    <!-- Tableau des événements -->
    <?php if (empty($events)): ?>
        <div class="empty-state">
            <p>Aucun événement ne correspond aux filtres sélectionnés.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Titre</th>
                        <th scope="col">Type</th>
                        <th scope="col">Date</th>
                        <th scope="col">Lieu</th>
                        <th scope="col">Soumis par</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr class="table-row table-row--<?= e($event['status']) ?>">
                            <td class="td-id">#<?= (int)$event['id'] ?></td>
                            <td class="td-title">
                                <strong><?= e($event['title']) ?></strong>
                                <button type="button" class="td-desc-toggle" onclick="toggleDesc(<?= (int)$event['id'] ?>)">
                                    Voir la description complète
                                    </button>
                                    <div class="td-desc-full" id="desc-<?= (int)$event['id'] ?>" hidden>
                                    <?= nl2br(e($event['description'])) ?>
                                    </div>
                            </td>
                            <td>
                                <span class="type-tag type-tag--<?= e($event['type']) ?>">
                                    <?= e(EVENT_TYPES[$event['type']] ?? $event['type']) ?>
                                </span>
                            </td>
                            <td><?= e(formatDateFr($event['event_date'])) ?></td>
                            <td><?= e($event['location']) ?></td>
                            <td><?= e($event['username']) ?></td>
                            <td>
                                <span class="status-pill status-pill--<?= e($event['status']) ?>">
                                    <?= e(EVENT_STATUSES[$event['status']] ?? $event['status']) ?>
                                </span>
                            </td>

                            <!-- Boutons d'action (chacun = formulaire POST sécurisé) -->
                            <td class="td-actions">
                                <a href="<?= APP_URL ?>/views/admin/edit_event.php?id=<?= (int)$event['id'] ?>"
                                    class="btn btn--neutral btn--sm" title="Modifier l'événement">
                                    ✎ Modifier
                                </a>
                                <?php if ($event['status'] !== 'accepte'): ?>
                                    <form method="POST" action="<?= APP_URL ?>/src/controllers/EventController.php">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="action"     value="update_status">
                                        <input type="hidden" name="event_id"   value="<?= (int)$event['id'] ?>">
                                        <input type="hidden" name="status"     value="accepte">
                                        <button type="submit" class="btn btn--accept btn--sm"
                                                title="Valider l'événement">
                                            ✓ Valider
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($event['status'] !== 'refuse'): ?>
                                    <form method="POST" action="<?= APP_URL ?>/src/controllers/EventController.php"
                                          onsubmit="return confirm('Refuser cet événement ?')">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="action"     value="update_status">
                                        <input type="hidden" name="event_id"   value="<?= (int)$event['id'] ?>">
                                        <input type="hidden" name="status"     value="refuse">
                                        <button type="submit" class="btn btn--refuse btn--sm"
                                                title="Refuser l'événement">
                                            ✕ Refuser
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($event['status'] !== 'en_attente'): ?>
                                    <form method="POST" action="<?= APP_URL ?>/src/controllers/EventController.php">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="action"     value="update_status">
                                        <input type="hidden" name="event_id"   value="<?= (int)$event['id'] ?>">
                                        <input type="hidden" name="status"     value="en_attente">
                                        <button type="submit" class="btn btn--neutral btn--sm"
                                                title="Remettre en attente">
                                            ↩ Attente
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</section>
<script>
function toggleDesc(id) {
    const el = document.getElementById('desc-' + id);
    const btn = el.previousElementSibling;
    if (el.hidden) {
        el.hidden = false;
        btn.textContent = 'Masquer la description';
    } else {
        el.hidden = true;
        btn.textContent = 'Voir la description complète';
    }
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>

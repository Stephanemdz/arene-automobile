<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../src/models/ContactModel.php';

requireLogin(adminOnly: true);

$pageTitle = 'Messages de contact';
$extraCss  = ['admin.css'];

$contactModel = new ContactModel(getPDO());

$unreadOnly = isset($_GET['unread']) && $_GET['unread'] === '1';
$messages   = $contactModel->getAll($unreadOnly);
$unreadCount = $contactModel->countUnread();

require_once __DIR__ . '/../partials/header.php';
?>

<section class="admin-section">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Messages de contact</h1>
            <p class="section-sub"><?= count($messages) ?> message(s) affiché(s)</p>
        </div>
        <div style="display:flex;gap:12px;align-items:center">
            <?php if ($unreadOnly): ?>
                <a href="<?= APP_URL ?>/views/admin/messages.php" class="btn btn--ghost btn--sm">
                    Voir tous les messages
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/views/admin/messages.php?unread=1" class="btn btn--ghost btn--sm">
                    Non lus uniquement
                    <?php if ($unreadCount > 0): ?>
                        <span class="status-badge status-badge--en_attente"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/views/admin/events.php" class="btn btn--ghost btn--sm">
                ← Retour aux événements
            </a>
        </div>
    </div>

    <!-- Résumé -->
    <div class="stats-row" style="margin-bottom:24px">
        <div class="stat-card">
            <div class="stat-card-num"><?= $contactModel->countUnread() ?></div>
            <div class="stat-card-lbl">Non lus</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-num"><?= count($contactModel->getAll()) ?></div>
            <div class="stat-card-lbl">Total</div>
        </div>
    </div>

    <div id="flash-messages" class="flash"></div>

    <?php if (empty($messages)): ?>
        <div class="empty-state">
            <p>Aucun message de contact pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap" style="border:1px solid var(--border-color);border-radius:var(--radius-lg)">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Email</th>
                        <th scope="col">Sujet</th>
                        <th scope="col">Message</th>
                        <th scope="col">Date</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr class="<?= $msg['is_read'] ? '' : 'row-pending' ?>">
                            <td class="td-id">#<?= (int)$msg['id'] ?></td>
                            <td style="font-weight:600;color:var(--color-chrome-bright)">
                                <?= e($msg['name']) ?>
                            </td>
                            <td style="font-size:13px">
                                <a href="mailto:<?= e($msg['email']) ?>"
                                   style="color:var(--color-red)">
                                    <?= e($msg['email']) ?>
                                </a>
                            </td>
                            <td style="max-width:180px;font-size:13px">
                                <?= e($msg['subject']) ?>
                            </td>
                            <td>
                                <button type="button" class="td-desc-toggle"
                                        onclick="toggleMsg(<?= (int)$msg['id'] ?>)">
                                    Lire le message
                                </button>
                                <div class="td-desc-full" id="msg-<?= (int)$msg['id'] ?>" hidden>
                                    <?= nl2br(e($msg['message'])) ?>
                                </div>
                            </td>
                            <td style="font-size:12px;white-space:nowrap">
                                <?= e(formatDateFr($msg['created_at'])) ?>
                            </td>
                            <td>
                                <?php if ($msg['is_read']): ?>
                                    <span class="status-pill status-pill--accepte">Lu</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill--en_attente">Non lu</span>
                                <?php endif; ?>
                            </td>
                            <td class="td-actions">
                                <?php if (!$msg['is_read']): ?>
                                    <form method="POST"
                                          action="<?= APP_URL ?>/src/controllers/ContactController.php">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="action"
                                               value="mark_as_read">
                                        <input type="hidden" name="message_id"
                                               value="<?= (int)$msg['id'] ?>">
                                        <button type="submit" class="btn btn--accept btn--sm">
                                            ✓ Marquer lu
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST"
                                      action="<?= APP_URL ?>/src/controllers/ContactController.php"
                                      onsubmit="return confirm('Supprimer ce message ?')">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="message_id"
                                           value="<?= (int)$msg['id'] ?>">
                                    <button type="submit" class="btn btn--refuse btn--sm">
                                        ✕ Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</section>

<script>
function toggleMsg(id) {
    const el  = document.getElementById('msg-' + id);
    const btn = el.previousElementSibling;
    if (el.hidden) {
        el.hidden = false;
        btn.textContent = 'Masquer le message';
    } else {
        el.hidden = true;
        btn.textContent = 'Lire le message';
    }
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
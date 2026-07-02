<?php

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/src/models/EventModel.php';

$pageTitle = 'Accueil';

$eventModel = new EventModel(getPDO());
$events     = $eventModel->getAccepted();

require_once __DIR__ . '/views/partials/header.php';
?>

<section class="hero">
    <div class="hero-inner">
        <p class="hero-eyebrow">🏁 La référence en France</p>
        <h1 class="hero-title">Tous les événements<br><span class="hero-accent">automobiles</span> au même endroit</h1>
        <p class="hero-sub">
            Rassemblements, salons, courses, track days, road trips —
            découvrez et soumettez les rendez-vous de la communauté. 
        </p>
        <div class="hero-actions">
            <a href="<?= APP_URL ?>/views/events/map.php" class="btn btn--primary btn--lg">Voir la carte</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/views/events/submit.php" class="btn btn--ghost btn--lg">Proposer un événement</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/auth/register.php" class="btn btn--ghost btn--lg">Rejoindre la communauté</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Liste des événements -->
<section class="events-section">
    <h2 class="section-title">Prochains événements</h2>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <p>Aucun événement à venir pour le moment. Soyez le premier à en proposer un !</p>
            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/views/events/submit.php" class="btn btn--primary" style="margin-top:1rem">
                    Proposer un événement
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <article class="event-card">
                    <?php if ($event['cover_image']): ?>
                        <div class="event-card__img">
                            <img
                                src="<?= APP_URL ?>/public/images/events/<?= e($event['cover_image']) ?>"
                                alt="<?= e($event['title']) ?>"
                                loading="lazy"
                            >
                        </div>
                    <?php endif; ?>
                    <div class="event-card__body">
                        <span class="type-tag type-tag--<?= e($event['type']) ?>">
                            <?= e(EVENT_TYPES[$event['type']] ?? $event['type']) ?>
                        </span>
                        <h3 class="event-card__title"><?= e($event['title']) ?></h3>
                        <p class="event-card__meta">
                            📅 <?= e(formatDateFr($event['event_date'])) ?>
                            <?php if ($event['event_time']): ?>
                                à <?= e(substr($event['event_time'], 0, 5)) ?>
                            <?php endif; ?>
                        </p>
                        <p class="event-card__location">📍 <?= e($event['location']) ?></p>
                        <div class="event-card__footer">
                           <a href="<?= APP_URL ?>/views/events/show.php?id=<?= (int)$event['id'] ?>"
                            class="btn btn--ghost btn--sm">Voir le détail →</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<style>
/* Styles inline de la homepage pour ne pas polluer les fichiers partagés */
.hero {
    padding       : var(--space-20) 0;
    text-align    : center;
    border-bottom : 1px solid var(--border-color);
}
.hero-eyebrow {
    font-size      : var(--text-sm);
    letter-spacing : 0.1em;
    text-transform : uppercase;
    color          : var(--color-red);
    font-weight    : 700;
    margin-bottom  : var(--space-4);
    max-width      : none;
}
.hero-title {
    font-size      : clamp(2.5rem, 6vw, 4.5rem);
    letter-spacing : 0.04em;
    margin-bottom  : var(--space-6);
}
.hero-accent { color: var(--color-red); }
.hero-sub    { font-size: var(--text-xl); color: var(--color-chrome-dim); margin: 0 auto var(--space-8); }
.hero-actions { display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap; }

.events-section { padding: var(--space-12) 0; }
.section-title  { margin-bottom: var(--space-8); font-size: var(--text-3xl); }

.events-grid {
    display               : grid;
    grid-template-columns : repeat(auto-fill, minmax(300px, 1fr));
    gap                   : var(--space-6);
}

.event-card {
    background    : var(--color-asphalt-mid);
    border        : 1px solid var(--border-color);
    border-radius : var(--radius-lg);
    overflow      : hidden;
    transition    : transform var(--transition-normal), box-shadow var(--transition-normal);
}
.event-card:hover {
    transform  : translateY(-4px);
    box-shadow : var(--shadow-md);
}
.event-card__img img { width: 100%; height: 180px; object-fit: cover; }
.event-card__body    { padding: var(--space-5); display: flex; flex-direction: column; gap: var(--space-2); }
.event-card__title   { font-size: var(--text-lg); color: var(--color-chrome-bright); white-space: normal; }
.event-card__meta,
.event-card__location { font-size: var(--text-sm); color: var(--color-chrome-dim); max-width: none; }
</style>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>

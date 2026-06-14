<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../src/models/EventModel.php';

$pageTitle = 'Carte des événements';
$extraCss  = ['map.css'];

$eventModel     = new EventModel(getPDO());
$acceptedEvents = $eventModel->getAccepted();

$eventsJson = json_encode($acceptedEvents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);

require_once __DIR__ . '/../partials/header.php';
?>

<section class="map-section">
    <div class="map-section__header">
        <h1 class="page-title">Carte des événements</h1>
        <p class="page-subtitle">
            <?= count($acceptedEvents) ?> événement(s) à venir en France
        </p>
    </div>

    <!-- Filtres rapides sur la carte -->
    <div class="map-filters" role="group" aria-label="Filtrer par type">
        <button class="map-filter-btn map-filter-btn--active" data-type="all">Tous</button>
        <?php foreach (EVENT_TYPES as $key => $label): ?>
            <button class="map-filter-btn" data-type="<?= e($key) ?>"><?= e($label) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Conteneur de la carte Leaflet -->
    <div id="events-map" class="leaflet-map" aria-label="Carte des événements automobiles"></div>
</section>

<!-- Leaflet depuis CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const EVENTS_DATA = <?= $eventsJson ?>;

// Initialisation de la carte centrée sur la France 
const map = L.map('events-map').setView([46.603354, 1.888334], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 18,
}).addTo(map);

const TYPE_COLORS = {
    rassemblement : '#e63946',
    salon         : '#457b9d',
    course        : '#f4a261',
    trackday      : '#2a9d8f',
    roadtrip      : '#8338ec',
};

function makeIcon(type) {
    const color = TYPE_COLORS[type] || '#333';
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="42" viewBox="0 0 32 42">
            <path d="M16 0C7.163 0 0 7.163 0 16c0 12 16 26 16 26s16-14 16-26C32 7.163 24.837 0 16 0z"
                  fill="${color}" stroke="#fff" stroke-width="2"/>
            <circle cx="16" cy="16" r="7" fill="#fff"/>
        </svg>`;
    return L.divIcon({
        html       : svg,
        className  : '',
        iconSize   : [32, 42],
        iconAnchor : [16, 42],
        popupAnchor: [0, -44],
    });
}

const markers = [];

EVENTS_DATA.forEach(ev => {
    if (!ev.latitude || !ev.longitude) return;

    const marker = L.marker([ev.latitude, ev.longitude], {
        icon      : makeIcon(ev.type),
        title     : ev.title,
        eventType : ev.type,
    });

    const dateStr = new Date(ev.event_date).toLocaleDateString('fr-FR', {
        day   : 'numeric',
        month : 'long',
        year  : 'numeric',
    });

    marker.bindPopup(`
        <div class="map-popup">
            <span class="popup-type popup-type--${ev.type}">${ev.type}</span>
            <h3 class="popup-title">${ev.title}</h3>
            <p class="popup-date">📅 ${dateStr}</p>
            <p class="popup-location">📍 ${ev.location}</p>
        </div>
    `, { minWidth: 220 });

    marker.addTo(map);
    markers.push(marker);
});

document.querySelectorAll('.map-filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const type = btn.dataset.type;

        document.querySelectorAll('.map-filter-btn')
            .forEach(b => b.classList.remove('map-filter-btn--active'));
        btn.classList.add('map-filter-btn--active');

        markers.forEach(m => {
            if (type === 'all' || m.options.eventType === type) {
                m.addTo(map);
            } else {
                m.remove();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

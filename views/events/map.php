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

    <div class="map-search-bar">
        <input
            type="text"
            id="city-search"
            class="form-input"
            placeholder="🔍 Rechercher une ville ou une adresse..."
            autocomplete="off"
        >
        <button class="btn btn--primary" onclick="searchCity()">Rechercher</button>
        <button class="btn btn--ghost" onclick="resetMap()">Réinitialiser</button>
    </div>
    <div id="search-suggestions" class="search-suggestions"></div>

    <div class="map-filters" role="group" aria-label="Filtrer par type">
        <button class="map-filter-btn map-filter-btn--active" data-type="all">Tous</button>
        <?php foreach (EVENT_TYPES as $key => $label): ?>
            <button class="map-filter-btn" data-type="<?= e($key) ?>"><?= e($label) ?></button>
        <?php endforeach; ?>
    </div>

    <div id="events-map" class="leaflet-map" aria-label="Carte des événements automobiles"></div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
const EVENTS_DATA = <?= $eventsJson ?>;

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

let markerCluster = L.markerClusterGroup({
    showCoverageOnHover: false,
    maxClusterRadius   : 60,
});
map.addLayer(markerCluster);

let allMarkers = [];

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
            <a href="<?= APP_URL ?>/views/events/show.php?id=${ev.id}" class="popup-link">Voir le détail →</a>
        </div>
    `, { minWidth: 220 });

    allMarkers.push(marker);
    markerCluster.addLayer(marker);
});

function applyFilter(type) {
    markerCluster.clearLayers();
    allMarkers.forEach(m => {
        if (type === 'all' || m.options.eventType === type) {
            markerCluster.addLayer(m);
        }
    });
}

document.querySelectorAll('.map-filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.map-filter-btn')
            .forEach(b => b.classList.remove('map-filter-btn--active'));
        btn.classList.add('map-filter-btn--active');
        applyFilter(btn.dataset.type);
    });
});

let searchMarker  = null;
let debounceTimer = null;
const searchInput    = document.getElementById('city-search');
const suggestionsBox = document.getElementById('search-suggestions');

searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const query = searchInput.value.trim();
    if (query.length < 3) {
        suggestionsBox.innerHTML = '';
        suggestionsBox.style.display = 'none';
        return;
    }
    debounceTimer = setTimeout(() => fetchSuggestions(query), 500);
});

async function fetchSuggestions(query) {
    const url = `https://nominatim.openstreetmap.org/search?` +
        new URLSearchParams({
            q                : query,
            format           : 'json',
            limit            : 5,
            countrycodes     : 'fr',
            'accept-language': 'fr',
        });
    try {
        const res  = await fetch(url);
        const data = await res.json();
        if (data.length === 0) {
            suggestionsBox.innerHTML = '<div class="suggestion-item">Aucun résultat</div>';
            suggestionsBox.style.display = 'block';
            return;
        }
        suggestionsBox.innerHTML = data.map(item => `
            <div class="suggestion-item"
                 onclick="selectSuggestion(${item.lat}, ${item.lon}, '${item.display_name.replace(/'/g, "\\'")}')">
                📍 ${item.display_name}
            </div>
        `).join('');
        suggestionsBox.style.display = 'block';
    } catch (err) {
        console.warn('Erreur Nominatim :', err);
    }
}

function selectSuggestion(lat, lng, name) {
    searchInput.value = name;
    suggestionsBox.innerHTML = '';
    suggestionsBox.style.display = 'none';
    flyToLocation(parseFloat(lat), parseFloat(lng));
}

async function searchCity() {
    const query = searchInput.value.trim();
    if (!query) return;
    const url = `https://nominatim.openstreetmap.org/search?` +
        new URLSearchParams({
            q                : query,
            format           : 'json',
            limit            : 1,
            countrycodes     : 'fr',
            'accept-language': 'fr',
        });
    try {
        const res  = await fetch(url);
        const data = await res.json();
        if (data.length > 0) {
            flyToLocation(parseFloat(data[0].lat), parseFloat(data[0].lon));
        }
    } catch (err) {
        console.warn('Erreur recherche :', err);
    }
}

function flyToLocation(lat, lng) {
    map.flyTo([lat, lng], 12, { duration: 1.2 });
    if (searchMarker) searchMarker.remove();
    searchMarker = L.marker([lat, lng], {
        icon: L.divIcon({
            html      : '<div class="search-pin"></div>',
            className : '',
            iconSize  : [16, 16],
            iconAnchor: [8, 8],
        })
    }).addTo(map);
}

function resetMap() {
    searchInput.value = '';
    suggestionsBox.innerHTML = '';
    suggestionsBox.style.display = 'none';
    if (searchMarker) { searchMarker.remove(); searchMarker = null; }
    map.flyTo([46.603354, 1.888334], 6, { duration: 1.2 });
}

document.addEventListener('click', e => {
    if (!e.target.closest('.map-search-bar') && !e.target.closest('#search-suggestions')) {
        suggestionsBox.style.display = 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
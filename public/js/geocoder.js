
'use strict';

(function () {
    const locationInput = document.getElementById('location');
    const latInput      = document.getElementById('latitude');
    const lngInput      = document.getElementById('longitude');
    const previewDiv    = document.getElementById('map-preview');

    if (!locationInput || !latInput || !lngInput || !previewDiv) return;

    // Carte Leaflet miniature
    let previewMap    = null;
    let previewMarker = null;

    function initPreviewMap(lat, lng) {
        previewDiv.classList.add('is-visible');

        if (!previewMap) {
            previewMap = L.map('map-preview', { zoomControl: true }).setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 18,
            }).addTo(previewMap);
        } else {
            previewMap.setView([lat, lng], 13);
        }

        if (previewMarker) {
            previewMarker.setLatLng([lat, lng]);
        } else {
            previewMarker = L.marker([lat, lng]).addTo(previewMap);
        }
    }

    // Géocodage avec debounce
    let debounceTimer = null;

    async function geocodeAddress(address) {
        if (address.trim().length < 5) return;

        const url = `https://nominatim.openstreetmap.org/search?` +
            new URLSearchParams({
                q              : address,
                format         : 'json',
                limit          : 1,
                countrycodes   : 'fr',     // Restreint à la France
                'accept-language': 'fr',
            });

        try {
            const res  = await fetch(url, {
                headers: { 'Accept-Language': 'fr' }
            });
            const data = await res.json();

            if (data.length > 0) {
                const { lat, lon } = data[0];
                latInput.value = parseFloat(lat).toFixed(7);
                lngInput.value = parseFloat(lon).toFixed(7);
                initPreviewMap(parseFloat(lat), parseFloat(lon));
            } else {
                latInput.value = '';
                lngInput.value = '';
            }
        } catch (err) {
            console.warn('[Géocodeur] Erreur Nominatim :', err);
        }
    }

    locationInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        // Attend 800 ms après la dernière frappe pour limiter les appels
        debounceTimer = setTimeout(() => {
            geocodeAddress(locationInput.value);
        }, 800);
    });

    if (locationInput.value.trim().length > 4) {
        geocodeAddress(locationInput.value);
    }
})();

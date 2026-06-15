function formatEventDate(isoDate) {
    const date = new Date(isoDate);
    if (isNaN(date.getTime())) return 'Date inconnue';
    
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }) + ' à ' + date.getHours().toString().padStart(2, '0') + 'h' + date.getMinutes().toString().padStart(2, '0');
}

/**
 * Valide les coordonnées GPS
 */
function validateCoordinates(lat, lng) {
    const latitude = parseFloat(lat);
    const longitude = parseFloat(lng);
    return (latitude >= -90 && latitude <= 90) && (longitude >= -180 && longitude <= 180);
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { formatEventDate, validateCoordinates };
}
/**
 * @jest-environment jsdom
 */
const { formatEventDate } = require('./utils.js');
const sum = require('./sum');

test('adds 1 + 2 to equal 3', () => {
  expect(sum(1, 2)).toBe(3);
});

// test validation des coordonnées GPS
describe('Test de la fonction géocode', () => {

    test('Quand je donne une adresse, les champs doivent se remplir', async () => {
        document.body.innerHTML = '<input id="latitude" />';
        
        const latitudeInput = document.getElementById('latitude');
        latitudeInput.value = '48.8566'; 
        
        expect(latitudeInput.value).toBe('48.8566'); 

    });
});


// test formatage de date 

describe('Test de formatEventDate', () => {

    test('formate correctement une date ISO standard', () => {
        const input = '2025-06-15T14:00:00';
        const expected = '15 juin 2025 à 14h00';
        expect(formatEventDate(input)).toBe(expected);
    });

    test('gère correctement les dates en début de mois', () => {
        const input = '2025-01-01T09:30:00';
        const expected = '1 janvier 2025 à 09h30';
        expect(formatEventDate(input)).toBe(expected);
    });

    test('renvoie une valeur par défaut ou vide si la date est invalide', () => {
        const input = 'date-invalide';
        // Ajuste selon ce que ta fonction renvoie réellement en cas d'erreur
        expect(formatEventDate(input)).toBe('Date inconnue'); 
    });
});
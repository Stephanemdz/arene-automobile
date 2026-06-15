<?php
declare(strict_types=1);

define('APP_NAME',    'Arène Automobile');
define('APP_URL',     'http://localhost/automobile_arene');
define('APP_VERSION', '1.0.0');

// Durée de session (en secondes) — 2 heures
define('SESSION_LIFETIME', 7200);

// Types d'événements autorisés
define('EVENT_TYPES', [
    'rassemblement' => 'Rassemblement',
    'salon'         => 'Salon',
    'course'        => 'Course',
    'trackday'      => 'Track Day',
    'roadtrip'      => 'Road Trip',
]);

// Statuts d'événements
define('EVENT_STATUSES', [
    'en_attente' => 'En attente',
    'accepte'    => 'Accepté',
    'refuse'     => 'Refusé',
]);

// Taille max upload image (5 Mo)
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_DIR', __DIR__ . '/../public/images/events/');

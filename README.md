# 🏁 Arène Automobile

Plateforme centralisant tous les événements automobiles en France.
Rassemblements · Salons · Courses · Track Days · Road Trips

---

## Installation

### 1. Base de données

```bash
mysql -u root -p < database/schema.sql
```

### 2. Configuration

Éditez `config/database.php` :
```php
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_mot_de_passe');
```

Éditez `config/app.php` :
```php
define('APP_URL', 'https://votre-domaine.fr');
```

### 3. Dossier uploads

```bash
mkdir -p public/images/events
chmod 755 public/images/events
```

### 4. Serveur de développement

```bash
php -S localhost:8000
# Puis ouvrir http://localhost:8000
```

---

## Sécurité implémentée

| Menace | Contre-mesure |
|--------|--------------|
| Injection SQL | Toutes les requêtes utilisent `PDO::prepare()` + paramètres liés |
| XSS | Tout affichage passe par `htmlspecialchars()` via `e()` |
| CSRF | Token aléatoire `bin2hex(random_bytes(32))` validé sur chaque POST |
| Fixation de session | `session_regenerate_id(true)` à la connexion |
| Accès non autorisé | `requireLogin(adminOnly: true)` en tête de chaque page protégée |
| Énumération d'emails | Message d'erreur de connexion volontairement vague |

---

## Dépendances externes (CDN — aucune installation npm)

| Bibliothèque | Usage | Version |
|---|---|---|
| [Leaflet](https://leafletjs.com) | Carte interactive | 1.9.4 |
| [OpenStreetMap](https://www.openstreetmap.org) | Tuiles cartographiques | — |
| [Nominatim](https://nominatim.org) | Géocodage d'adresses | — |
| [Google Fonts](https://fonts.google.com) | Rajdhani + Inter | — |

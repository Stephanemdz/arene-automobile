# 🏁 Arène Automobile

Plateforme centralisant tous les événements automobiles en France.
Rassemblements · Salons · Courses · Track Days · Road Trips

---

## Structure du projet

```
arene-automobile/
│
├── config/                         # Configuration — jamais exposé publiquement
│   ├── app.php                     # Constantes globales (APP_URL, types, statuts…)
│   ├── database.php                # Connexion PDO singleton + constantes DB
│   └── bootstrap.php              # Point d'entrée : charge tout + démarre la session
│
├── src/                            # Logique métier (PHP pur, aucun HTML)
│   ├── controllers/
│   │   ├── EventController.php     # Soumission + changement de statut
│   │   └── AuthController.php      # Connexion, inscription, déconnexion
│   ├── models/
│   │   ├── EventModel.php          # Requêtes PDO préparées sur `events`
│   │   └── UserModel.php           # Requêtes PDO préparées sur `users`
│   └── helpers/
│       ├── auth.php                # isLoggedIn(), isAdmin(), CSRF, requireLogin()
│       └── functions.php          # e(), redirect(), flash, handleImageUpload(), formatDateFr()
│
├── views/                          # Templates PHP (HTML + echo seulement)
│   ├── partials/
│   │   ├── header.php              # <head>, nav, flash message
│   │   └── footer.php             # Fermeture des balises, scripts JS
│   ├── events/
│   │   ├── submit.php              # Formulaire de soumission d'événement
│   │   └── map.php                # Carte interactive Leaflet
│   └── admin/
│       └── events.php             # Back-office : tableau + filtres + boutons statut
│
├── public/                         # Seul dossier exposé par le serveur web
│   ├── css/
│   │   ├── reset.css              # Normalisation navigateurs (sans dépendances)
│   │   ├── variables.css          # Design tokens : couleurs, typographie, espacement
│   │   ├── typography.css         # Corps de texte, titres, familles de polices
│   │   ├── layout.css             # Header, footer, container, grille de page
│   │   ├── components.css         # Boutons, alertes, badges de statut, type-tags
│   │   ├── form.css               # Tous les éléments de formulaires
│   │   ├── map.css                # Carte Leaflet, popups, filtres de carte
│   │   └── admin.css              # Tableau admin, filtres, styles back-office
│   ├── js/
│   │   └── geocoder.js            # Géocodage Nominatim + aperçu Leaflet
│   └── images/
│       └── events/                # Images uploadées par les utilisateurs
│
├── auth/
│   ├── login.php                  # Page de connexion (vue)
│   └── register.php              # Page d'inscription (vue)
│
├── database/
│   └── schema.sql                 # Script SQL complet (CREATE + INDEX + seed admin)
│
├── index.php                      # Page d'accueil (hero + liste événements)
└── USER_STORIES.md               # Backlog priorisé (format Agile)
```

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

## Compte admin par défaut

| Email | Mot de passe |
|-------|-------------|
| admin@arene-automobile.fr | `Admin1234!` |

⚠️ **Changez ce mot de passe immédiatement en production.**

---

## Sécurité implémentée

| Menace | Contre-mesure |
|--------|--------------|
| Injection SQL | Toutes les requêtes utilisent `PDO::prepare()` + paramètres liés |
| XSS | Tout affichage passe par `htmlspecialchars()` via `e()` |
| CSRF | Token aléatoire `bin2hex(random_bytes(32))` validé sur chaque POST |
| Fixation de session | `session_regenerate_id(true)` à la connexion |
| Upload malveillant | Validation MIME réelle (`finfo`), extension, taille, nom aléatoire |
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

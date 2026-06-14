<?php
declare(strict_types=1);

/** Vérifie si un utilisateur est connecté. */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/** Vérifie si l'utilisateur connecté est admin. */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function requireLogin(bool $adminOnly = false): void
{
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
    if ($adminOnly && !isAdmin()) {
        header('Location: ' . APP_URL . '/index.php?error=forbidden');
        exit;
    }
}

/** Génère (ou retourne) un token CSRF stocké en session. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Valide le token CSRF soumis dans un formulaire POST. */
function verifyCsrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted)) {
        http_response_code(403);
        die('Token CSRF invalide. Rechargez la page et réessayez.');
    }
}

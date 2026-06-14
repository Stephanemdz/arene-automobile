<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirige et stoppe l'exécution. */
function redirect(string $path): never
{
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Flashe un message en session.
 * @param 'success'|'error'|'warning' $type
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}

/** Récupère et efface le message flash. */
function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Valide et déplace une image uploadée.
 * Retourne le nom de fichier ou null en cas d'erreur.
 */
function handleImageUpload(array $file): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        return null;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    return $filename;
}

function formatDateFr(string $date): string
{
    $months = [
        '01' => 'janvier', '02' => 'février', '03' => 'mars',
        '04' => 'avril',   '05' => 'mai',      '06' => 'juin',
        '07' => 'juillet', '08' => 'août',     '09' => 'septembre',
        '10' => 'octobre', '11' => 'novembre', '12' => 'décembre',
    ];
    [$y, $m, $d] = explode('-', $date);
    return (int)$d . ' ' . $months[$m] . ' ' . $y;
}

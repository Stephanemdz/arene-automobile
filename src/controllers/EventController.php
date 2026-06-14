<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';

class EventController
{
    private EventModel $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel(getPDO());
    }

    // Action : soumettre un événement (visiteur connecté)
    public function submit(): void
    {
        requireLogin();
        verifyCsrf();

        $errors = $this->validateSubmitForm($_POST);

        if (!empty($errors)) {
            $_SESSION['form_errors']  = $errors;
            $_SESSION['form_old']     = $_POST;
            redirect('views/events/submit.php');
        }

        $coverImage = null;
        if (!empty($_FILES['cover_image']['name'])) {
            $coverImage = handleImageUpload($_FILES['cover_image']);
            if ($coverImage === null) {
                setFlash('error', 'Image invalide (format ou taille non accepté).');
                redirect('views/events/submit.php');
            }
        }

        $this->eventModel->create([
            'user_id'     => (int) $_SESSION['user_id'],
            'title'       => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'event_date'  => $_POST['event_date'],
            'event_time'  => $_POST['event_time'] ?: null,
            'location'    => trim($_POST['location']),
            'latitude'    => $_POST['latitude']  !== '' ? (float) $_POST['latitude']  : null,
            'longitude'   => $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null,
            'type'        => $_POST['type'],
            'cover_image' => $coverImage,
        ]);

        setFlash('success', 'Votre événement a été soumis et est en attente de validation.');
        redirect('index.php');
    }

    // Action : modifier le statut (admin uniquement)
    public function updateStatus(): void
    {
        requireLogin(adminOnly: true);
        verifyCsrf();

        $id     = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';

        if (!$id || !in_array($status, ['accepte', 'refuse', 'en_attente'], true)) {
            setFlash('error', 'Requête invalide.');
            redirect('views/admin/events.php');
        }

        $ok = $this->eventModel->updateStatus($id, $status);

        if ($ok) {
            setFlash('success', 'Statut mis à jour avec succès.');
        } else {
            setFlash('error', 'Événement introuvable.');
        }

        redirect('views/admin/events.php');
    }

    // Validation du formulaire de soumission
    private function validateSubmitForm(array $post): array
    {
        $errors = [];

        if (empty(trim($post['title'] ?? '')) || strlen($post['title']) > 180) {
            $errors['title'] = 'Le titre est requis (180 caractères max).';
        }

        if (empty(trim($post['description'] ?? ''))) {
            $errors['description'] = 'La description est requise.';
        }

        if (empty($post['event_date']) || !strtotime($post['event_date'])) {
            $errors['event_date'] = 'Une date valide est requise.';
        } elseif ($post['event_date'] < date('Y-m-d')) {
            $errors['event_date'] = 'La date doit être dans le futur.';
        }

        if (empty(trim($post['location'] ?? ''))) {
            $errors['location'] = 'Le lieu est requis.';
        }

        $allowedTypes = array_keys(EVENT_TYPES);
        if (!in_array($post['type'] ?? '', $allowedTypes, true)) {
            $errors['type'] = 'Type d\'événement invalide.';
        }

        if (!empty($post['latitude']) && !is_numeric($post['latitude'])) {
            $errors['latitude'] = 'Latitude invalide.';
        }
        if (!empty($post['longitude']) && !is_numeric($post['longitude'])) {
            $errors['longitude'] = 'Longitude invalide.';
        }

        return $errors;
    }

    public function edit(): void
{
    requireLogin(adminOnly: true);
    verifyCsrf();

    $id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    if (!$id) {
        setFlash('error', 'Événement invalide.');
        redirect('views/admin/events.php');
    }

    $errors = $this->validateSubmitForm($_POST);

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $_POST;
        redirect('views/admin/edit_event.php?id=' . $id);
    }

    $this->eventModel->update($id, [
        'title'       => trim($_POST['title']),
        'description' => trim($_POST['description']),
        'event_date'  => $_POST['event_date'],
        'event_time'  => $_POST['event_time'] ?: null,
        'location'    => trim($_POST['location']),
        'latitude'    => $_POST['latitude']  !== '' ? (float) $_POST['latitude']  : null,
        'longitude'   => $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null,
        'type'        => $_POST['type'],
    ]);

    setFlash('success', 'Événement mis à jour avec succès.');
    redirect('views/admin/events.php');
}
}

// Routage minimal : action déterminée par le paramètre POST
$action     = $_POST['action'] ?? '';
$controller = new EventController();

match($action) {
    'submit'        => $controller->submit(),
    'update_status' => $controller->updateStatus(),
    'edit'          => $controller->edit(),
    default         => redirect('index.php'),
};

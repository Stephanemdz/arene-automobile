<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../models/ContactModel.php';

class ContactController
{
    private ContactModel $contactModel;

    public function __construct()
    {
        $this->contactModel = new ContactModel(getPDO());
    }

    public function send(): void
    {
        verifyCsrf();

        $errors = $this->validateForm($_POST);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $_POST;
            redirect('views/contact/index.php');
        }

        $this->contactModel->create([
            'name'    => trim($_POST['name']),
            'email'   => trim($_POST['email']),
            'subject' => trim($_POST['subject']),
            'message' => trim($_POST['message']),
        ]);

        setFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
        redirect('views/contact/index.php');
    }

    public function markAsRead(): void
    {
        requireLogin(adminOnly: true);
        verifyCsrf();

        $id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);

        if (!$id) {
            setFlash('error', 'Message invalide.');
            redirect('views/admin/messages.php');
        }

        $this->contactModel->markAsRead($id);
        setFlash('success', 'Message marqué comme lu.');
        redirect('views/admin/messages.php');
    }

    public function delete(): void
    {
        requireLogin(adminOnly: true);
        verifyCsrf();

        $id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);

        if (!$id) {
            setFlash('error', 'Message invalide.');
            redirect('views/admin/messages.php');
        }

        $this->contactModel->delete($id);
        setFlash('success', 'Message supprimé.');
        redirect('views/admin/messages.php');
    }

    private function validateForm(array $post): array
    {
        $errors = [];

        if (empty(trim($post['name'] ?? '')) || strlen($post['name']) > 120) {
            $errors['name'] = 'Le nom est requis (120 caractères max).';
        }

        if (!filter_var($post['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }

        if (empty(trim($post['subject'] ?? '')) || strlen($post['subject']) > 255) {
            $errors['subject'] = 'Le sujet est requis (255 caractères max).';
        }

        if (empty(trim($post['message'] ?? ''))) {
            $errors['message'] = 'Le message est requis.';
        }

        return $errors;
    }
}

$action     = $_POST['action'] ?? '';
$controller = new ContactController();

match($action) {
    'send'         => $controller->send(),
    'mark_as_read' => $controller->markAsRead(),
    'delete'       => $controller->delete(),
    default        => redirect('views/contact/index.php'),
};
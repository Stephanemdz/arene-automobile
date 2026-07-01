<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../models/CommentModel.php';
class CommentController
{
    private CommentModel $commentModel;

    public function __construct()
    {
        $this->commentModel = new CommentModel(getPDO());
    }

    public function create(): void
    {
        requireLogin();
        verifyCsrf();

        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $content = trim($_POST['content'] ?? '');

        if (!$eventId) {
            setFlash('error', 'Événement invalide.');
            redirect('views/events/show.php?id=' . $eventId);
        }

        if (empty($content)) {
            setFlash('error', 'Le commentaire ne peut pas être vide.');
            redirect('views/events/show.php?id=' . $eventId);
        }

        if (strlen($content) > 1000) {
            setFlash('error', 'Le commentaire ne peut pas dépasser 1000 caractères.');
            redirect('views/events/show.php?id=' . $eventId);
        }

        $this->commentModel->create(
            (int) $_SESSION['user_id'],
            $eventId,
            $content
        );

        setFlash('success', 'Commentaire publié avec succès.');
        redirect('views/events/show.php?id=' . $eventId);
    }

    public function delete(): void
    {
        requireLogin();
        verifyCsrf();

        $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
        $eventId   = filter_input(INPUT_POST, 'event_id',   FILTER_VALIDATE_INT);

        if (!$commentId || !$eventId) {
            setFlash('error', 'Requête invalide.');
            redirect('views/events/show.php?id=' . $eventId);
        }

        $ok = $this->commentModel->delete(
            $commentId,
            (int) $_SESSION['user_id'],
            isAdmin()
        );

        if ($ok) {
            setFlash('success', 'Commentaire supprimé.');
        } else {
            setFlash('error', 'Impossible de supprimer ce commentaire.');
        }

        redirect('views/events/show.php?id=' . $eventId);
    }
}


$action     = $_POST['action'] ?? '';
$controller = new CommentController();

match($action) {
    'create' => $controller->create(),
    'delete' => $controller->delete(),
    default  => redirect('index.php'),
};
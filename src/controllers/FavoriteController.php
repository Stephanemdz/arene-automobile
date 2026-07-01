<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../models/FavoriteModel.php';

class FavoriteController
{
    private FavoriteModel $favoriteModel;

    public function __construct()
    {
        $this->favoriteModel = new FavoriteModel(getPDO());
    }

    public function toggle(): void
    {
        requireLogin();
        verifyCsrf();

        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

        if (!$eventId) {
            setFlash('error', 'Événement invalide.');
            redirect('views/events/show.php?id=' . $eventId);
        }

        $userId     = (int) $_SESSION['user_id'];
        $isFavorite = $this->favoriteModel->isFavorite($userId, $eventId);

        if ($isFavorite) {
            $this->favoriteModel->remove($userId, $eventId);
            setFlash('success', 'Événement retiré de vos favoris.');
        } else {
            $this->favoriteModel->add($userId, $eventId);
            setFlash('success', 'Événement ajouté à vos favoris.');
        }

        redirect('views/events/show.php?id=' . $eventId);
    }
}

$action     = $_POST['action'] ?? '';
$controller = new FavoriteController();

match($action) {
    'toggle' => $controller->toggle(),
    default  => redirect('index.php'),
};
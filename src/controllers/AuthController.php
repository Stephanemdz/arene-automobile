<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel(getPDO());
    }



    // Connexion
    public function login(): void
    {

        verifyCsrf();

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            setFlash('error', 'Identifiants invalides.');
            redirect('auth/login.php');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            // Message volontairement vague
            setFlash('error', 'Email ou mot de passe incorrect.');
            redirect('auth/login.php');
        }

        // Régénérer l'ID de session
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        redirect($user['role'] === 'admin' ? 'views/admin/events.php' : 'index.php');
    }

    // Inscription
    public function register(): void
    {
        verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['confirm']       ?? '';

        $errors = [];

        if (strlen($username) < 3 || strlen($username) > 60) {
            $errors[] = 'Le pseudo doit faire entre 3 et 60 caractères.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit faire au moins 8 caractères.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (empty($errors)) {
            if ($this->userModel->findByEmail($email)) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
            if ($this->userModel->findByUsername($username)) {
                $errors[] = 'Ce pseudo est déjà pris.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['username' => $username, 'email' => $email];
            redirect('auth/register.php');
        }

        $userId = $this->userModel->create($username, $email, $password);

        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['username']  = $username;
        $_SESSION['user_role'] = 'visitor';

        setFlash('success', 'Compte créé ! Bienvenue sur Arène Automobile.');
        redirect('index.php');
    }

    // Déconnexion
    public function logout(): void
    {
        session_unset();
        session_destroy();
        redirect('auth/login.php');
    }
}

// Routage
$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new AuthController();

match($action) {
    'login'    => $controller->login(),
    'register' => $controller->register(),
    'logout'   => $controller->logout(),
    default    => redirect('index.php'),
};

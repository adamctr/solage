<?php

declare(strict_types=1);

class SessionManager
{
 // Renommé pour être logique avec son rôle

    protected $user = null;
    private $userModel;

    // 1. INJECTION DE DÉPENDANCE : On passe le modèle au constructeur
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            // Utilisation du modèle injecté
            $this->user = $this->userModel->getUserById($_SESSION['user_id']);
        }
    }

    public function login($userId)
    {
        $user = $this->userModel->getUserById($userId);

        if ($user) {
            $this->user = $user;

            $_SESSION['user_id'] = $userId;
            $_SESSION['name'] = $user->getName();
            $_SESSION['image'] = $user->getImage();
            $_SESSION['role'] = $user->getRole();
        }
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    // 2. TOUT EN NON-STATIQUE pour la cohérence de l'objet
    public function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getName()
    {
        return $_SESSION['name'] ?? null;
    }

    public function getImage()
    {
        return $_SESSION['image'] ?? null;
    }

    public function logout()
    {
        $this->user = null;
        session_unset();
        session_destroy();
    }

    public function getUser()
    {
        return $this->user;
    }

    public function isAdmin(): bool
    {
        return $this->user !== null && $this->user->getRoleName() === 'Admin';
    }
}

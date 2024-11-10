<?php

class SessionController {

    protected $user;

    public function __construct() {
        // Démarre une session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifie si l'utilisateur est déjà connecté
        if (isset($_SESSION['user_id'])) {
            // Si l'utilisateur est déjà connecté, on peut le charger
            $this->user = (new UserModel())->getUserById($_SESSION['user_id']);
        }
    }

    // Méthode pour se connecter
    public function login($userId) {
        $userModel = new UserModel();
        $user = $userModel->getUserById($userId);

        if ($user) {
            $this->user = $user;

            $_SESSION['user_id'] = $userId;
            $_SESSION['name'] = $user->getName();
            $_SESSION['image'] = $user->getImage();
            $_SESSION['role'] = $user->getRole();

        }
    }

    // Méthode pour vérifier si un utilisateur est connecté
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Méthode pour récupérer l'ID de l'utilisateur connecté
    static public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    // Méthode pour récupérer le nom d'utilisateur
    public function getName() {
        return $_SESSION['name'] ?? null;
    }

    public function getImage() {
        return $_SESSION['image'] ?? null;
    }

    // Méthode pour se déconnecter
    public function logout() {
        // Détruit la session
        $this->user = null;
        session_unset();
        session_destroy();
    }

    // Méthode pour obtenir l'objet utilisateur
    public function getUser() {
        return $this->user;
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

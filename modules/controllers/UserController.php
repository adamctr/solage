<?php

class UserController {
    protected $user;

    // Le constructeur prend maintenant l'ID de l'utilisateur en paramètre
    public function __construct($userId = null) {
        if ($userId !== null) {
            $userModel = new UserModel();
            $this->user = $userModel->getUserById($userId); // On récupère l'utilisateur avec l'ID
        }
    }

    public function execute($userId) {
        // On passe les informations de l'utilisateur à la vue
        $view = new UserView($this->user);
        $view->show();
    }

    public function showLoginForm() {
        // Affiche le formulaire de connexion
        $view = new UserView();
        $view->showLoginForm();
    }

    public function showRegisterForm() {
        // Affiche le formulaire d'inscription
        $view = new UserView();
        $view->showRegisterForm();
    }

    public function login() {
        $userModel = new UserModel();

        $email = trim($_POST['email']) ?? '';
        $password = trim($_POST['password']) ?? '';

        $validation = ValidatorController::login($email, $password);

        if ($validation) {
            $user = $userModel->getUserByEmail($email);
            $sessionController = new SessionController();
            $sessionController->login($user->getId());
            //header("Location: /");
            //exit;
        }
    }


    public function register() {
        $userModel = new UserModel();

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $validation = ValidatorController::register($email, $password);

        if ($validation) {
            $userModel->createUser($name, $email, $password);
        }


    }

    static public function logout() {
        // Déconnecte l'utilisateur en détruisant la session
        $sessionController = new SessionController();
        $sessionController->logout();

        // Redirige vers la page de connexion après déconnexion
        header("Location: /login");
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processUpdate(); // Si la méthode est POST, on traite la mise à jour
        } else {
            $this->showEditForm(); // Sinon, on affiche le formulaire
        }
    }

    // Affiche le formulaire d'édition
    protected function showEditForm() {
        $view = new EditUserView($this->user);
        $view->show();
    }

    // Traite la mise à jour de l'utilisateur
    protected function processUpdate() {
        $name = $_POST['name'] ?? null;
        $password = $_POST['password'] ?? null;

        // Mise à jour des informations dans la base de données
        $userModel = new UserModel();
        $userModel->updateUser($this->user->getId(), $name, $password);

        // Redirection après la mise à jour
        header('Location: /user/' . $this->user->getId());
        exit();
    }

    public function delete() {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);

        // Vérification de l'ID du post
        if (!isset($data['userId']) || empty($data['userId'])) {
            Utils::sendResponse(false, "ID de l'utilisateur manquant ou invalide");
            return;
        }

        $userId = $data['userId'];
        $userModel = new UserModel();
        // Appeler la méthode deleteUser dans le modèle pour supprimer l'utilisateur
        $result = $userModel->deleteUser($userId);

        if ($result) {
            // Si la suppression réussit, redirige vers la page d'administration ou une autre page pertinente
            Utils::sendResponse(true, 'Utilisateur bien supprimé');
            //header("Location: /admin");
            exit();
        } else {
            // Si la suppression échoue, affiche un message d'erreur
            Utils::sendResponse(false, "Une erreur est survenue lors de la suppression de l'utilisateur");
        }
    }

}

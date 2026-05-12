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
        $posts = PostModel::getAllPostsByUserId($this->user->getId());
        $users = [$this->user->getId() => $this->user];

        $view = new UserView($this->user, $posts, $users);
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
        $email = trim($_POST['email']) ?? '';
        $password = trim($_POST['password']) ?? '';

        $result = UserValidator::login($email, $password);
        header('Content-Type: application/json');
        Utils::sendResponse($result['ok'], $result['message']);

        if ($result['ok']) {
            $user = (new UserModel())->getUserByEmail($email);
            (new SessionController())->login($user->getId());
        }
    }


    public function register() {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = UserValidator::register($email, $password);
        header('Content-Type: application/json');
        Utils::sendResponse($result['ok'], $result['message']);

        if ($result['ok']) {
            (new UserModel())->createUser($name, $email, $password);
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
        $session = new SessionController();
        $currentUserId = SessionController::getUserId();
        $targetUserId  = $this->user?->getId();

        // Auth : le user doit exister et être soit le propriétaire, soit admin.
        if ($targetUserId === null || ($currentUserId !== $targetUserId && !$session->isAdmin())) {
            Logger::get()->warning('user.update.forbidden', [
                'current_user_id' => $currentUserId,
                'target_user_id'  => $targetUserId,
            ]);
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo "403 — vous ne pouvez pas modifier ce profil.";
            exit;
        }

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

        $userId = (int) $data['userId'];

        // Auth : seul le propriétaire du compte ou un admin peut supprimer.
        $session = new SessionController();
        $currentUserId = SessionController::getUserId();
        if ($currentUserId !== $userId && !$session->isAdmin()) {
            Logger::get()->warning('user.delete.forbidden', [
                'current_user_id' => $currentUserId,
                'target_user_id'  => $userId,
            ]);
            http_response_code(403);
            Utils::sendResponse(false, "Vous n'avez pas la permission de supprimer ce compte.");
            return;
        }

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

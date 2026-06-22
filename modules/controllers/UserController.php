<?php

declare(strict_types=1);

/**
 * Gère les utilisateurs : profil, connexion, inscription, édition, suppression.
 */
class UserController
{
    protected $user;

    /**
     * Charge l'utilisateur ciblé si un identifiant est fourni (paramètre d'URL).
     *
     * @param int|string|null $userId Identifiant de l'utilisateur, ou null.
     */
    public function __construct($userId = null)
    {
        if ($userId !== null) {
            $userModel = new UserModel();
            $this->user = $userModel->getUserById($userId); // On récupère l'utilisateur avec l'ID
        }
    }

    /**
     * Affiche le profil de l'utilisateur chargé et ses posts.
     *
     * @param int|string $userId Identifiant d'URL (le profil est déjà chargé au constructeur).
     * @return void
     */
    public function execute($userId)
    {
        $posts = PostModel::getAllPostsByUserId($this->user->getId());
        $users = [$this->user->getId() => $this->user];

        $view = new UserView($this->user, $posts, $users);
        $view->show();
    }

    /**
     * Affiche le formulaire de connexion.
     *
     * @return void
     */
    public function showLoginForm()
    {
        $view = new UserView();
        $view->showLoginForm();
    }

    /**
     * Affiche le formulaire d'inscription.
     *
     * @return void
     */
    public function showRegisterForm()
    {
        $view = new UserView();
        $view->showRegisterForm();
    }

    /**
     * Valide la connexion (JSON) et ouvre la session si les identifiants sont bons.
     *
     * @return void
     */
    public function login()
    {
        $email = trim($_POST['email']) ?? '';
        $password = trim($_POST['password']) ?? '';

        $result = UserValidator::login($email, $password);
        header('Content-Type: application/json');
        Utils::sendResponse($result['ok'], $result['message']);

        if ($result['ok']) {
            $userModel = new UserModel();
            $user = $userModel->getUserByEmail($email);
            (new SessionManager($userModel))->login($user->getId());
        }
    }


    /**
     * Valide l'inscription (JSON) et crée le compte si l'email est libre.
     *
     * @return void
     */
    public function register()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = UserValidator::register($email, $password);
        header('Content-Type: application/json');
        Utils::sendResponse($result['ok'], $result['message']);

        if ($result['ok']) {
            $userModel = new UserModel();
            $userModel->createUser($name, $email, $password);

            // Ouvre directement la session du nouveau compte (même mécanisme que
            // login) pour qu'il arrive connecté sur le fil après l'inscription.
            $newUser = $userModel->getUserByEmail($email);
            (new SessionManager($userModel))->login($newUser->getId());
        }
    }

    /**
     * Détruit la session et redirige vers la page de connexion.
     *
     * @return void
     */
    public static function logout()
    {
        $session = new SessionManager(new UserModel());
        $session->logout();

        // Redirige vers la page de connexion après déconnexion
        header("Location: /login");
        exit;
    }

    /**
     * Met à jour le profil : vérifie l'autorisation, puis traite le POST ou
     * affiche le formulaire d'édition.
     *
     * @return void
     */
    public function update()
    {
        $session = new SessionManager(new UserModel());
        $currentUserId = $session->getUserId();
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

    /**
     * Affiche le formulaire d'édition du profil chargé.
     *
     * @return void
     */
    protected function showEditForm()
    {
        $view = new EditUserView($this->user);
        $view->show();
    }

    /**
     * Traite la soumission du formulaire d'édition (nom + mot de passe).
     *
     * @return void
     */
    protected function processUpdate()
    {
        $name = $_POST['name'] ?? null;
        $password = $_POST['password'] ?? null;

        // Mise à jour des informations dans la base de données
        $userModel = new UserModel();
        $userModel->updateUser($this->user->getId(), $name, $password);

        // Resynchronise le pseudo en session (uniquement si on édite son propre profil)
        $session = new SessionManager($userModel);
        if ($session->getUserId() === $this->user->getId()) {
            $session->refreshName($name);
        }

        // Redirection après la mise à jour
        header('Location: /user/' . $this->user->getId());
        exit();
    }

    /**
     * Supprime un compte si l'utilisateur connecté en est le propriétaire ou un
     * admin. Renvoie le résultat en JSON.
     *
     * @return void
     */
    public function delete()
    {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);

        // Vérification de l'ID du post
        if (!isset($data['userId']) || empty($data['userId'])) {
            Utils::sendResponse(false, "ID de l'utilisateur manquant ou invalide");
            return;
        }

        $userId = (int) $data['userId'];

        // Auth : seul le propriétaire du compte ou un admin peut supprimer.
        $userModel = new UserModel();
        $session = new SessionManager($userModel);
        $currentUserId = $session->getUserId();
        if ($currentUserId !== $userId && !$session->isAdmin()) {
            Logger::get()->warning('user.delete.forbidden', [
                'current_user_id' => $currentUserId,
                'target_user_id'  => $userId,
            ]);
            http_response_code(403);
            Utils::sendResponse(false, "Vous n'avez pas la permission de supprimer ce compte.");
            return;
        }

        // Appeler la méthode deleteUser dans le modèle pour supprimer l'utilisateur
        $result = $userModel->deleteUser($userId);

        if ($result) {
            // Si la suppression réussit, redirige vers la page d'administration ou une autre page pertinente
            Utils::sendResponse(true, 'Utilisateur bien supprimé');
            exit();
        } else {
            // Si la suppression échoue, affiche un message d'erreur
            Utils::sendResponse(false, "Une erreur est survenue lors de la suppression de l'utilisateur");
        }
    }
}

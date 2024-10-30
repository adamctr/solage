<?php

class UserController {
    protected $user;

    // Le constructeur prend maintenant l'ID de l'utilisateur en paramètre
    public function __construct($userId = null) {
        if ($userId) {
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

        $user = $userModel->getUserByEmail($email);

        if ($user) {
            // Debugging: Affichez les mots de passe pour vérifier
            echo "Mot de passe saisi : " . htmlspecialchars($password) . "<br>";
            echo "Mot de passe haché en base de données : " . htmlspecialchars($user->getPassword()) . "<br>";

            // Vérification du mot de passe
            if (password_verify($password, $user->getPassword())) {
                // Authentification réussie

                $sessionController = new SessionController();
                $sessionController->login($user->getId());
                header("Location: /");
                exit;
            } else {
                // Mot de passe incorrect
                echo "Mot de passe incorrect. Veuillez réessayer.";
            }
        } else {
            // Aucune correspondance pour l'adresse e-mail
            echo "Aucun utilisateur trouvé avec cet e-mail. Veuillez vérifier vos identifiants.";
        }
    }


    public function register() {
        $userModel = new UserModel();

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($userModel->getUserByEmail($email)) {
            echo "Email déjà utilisé";
            return;
        }

        $userModel->createUser($name, $email, $password);

        header("Location: /login");
        exit;
    }

    public function logout() {
        // Déconnecte l'utilisateur en détruisant la session
        $sessionController = new SessionController();
        $sessionController->logout();

        // Redirige vers la page de connexion après déconnexion
        header("Location: /login");
        exit;
    }
}

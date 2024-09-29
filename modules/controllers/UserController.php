<?php

class UserController {
    protected $user;

    // Le constructeur prend maintenant l'ID de l'utilisateur en paramètre
    public function __construct($userId) {
        $userModel = new UserModel();
        $this->user = $userModel->getUserById($userId); // On récupère l'utilisateur avec l'ID
    }

    public function execute($userId) {
        // On passe les informations de l'utilisateur à la vue
        $view = new UserView($this->user);
        $view->show();
    }
}

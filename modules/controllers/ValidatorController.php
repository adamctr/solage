<?php

class ValidatorController {

    static public function login($email, $password) {

        if(empty($email) || empty($password)) {
            DynamicMessageController::showMessage('error', "Merci de renseigner vos informations");
            return false;
        }

        $userModel = new UserModel();
        $user = $userModel->getUserByEmail($email);

        if ($user) {
            if (password_verify($password, $user->getPassword())) {
                DynamicMessageController::showMessage('success', "Vous vous êtes bien connecté !");
                return true;
            } else {
                DynamicMessageController::showMessage('error', "Le mot de passe ne correspond pas");
                return false;
            }
        } else {
            DynamicMessageController::showMessage('error', "L'email n'existe pas");
            return false;
        }
    }

    static public function register($email, $password) {

        if(empty($email) || empty($password)) {
            DynamicMessageController::showMessage('error', "Merci de renseigner vos informations");
            return false;
        }

        $userModel = new UserModel();
        $user = $userModel->getUserByEmail($email);

        if ($user) {
            DynamicMessageController::showMessage('error', "L'email est déjà utilisé");
            return false;
        } else {
            DynamicMessageController::showMessage('success', "Inscription réussie");
            return true;
        }
    }
}

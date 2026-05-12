<?php

class ValidatorController {

    static public function login($email, $password): array {
        if (empty($email) || empty($password)) {
            return ['ok' => false, 'type' => 'error', 'message' => "Merci de renseigner vos informations"];
        }

        $user = (new UserModel())->getUserByEmail($email);

        if (!$user) {
            return ['ok' => false, 'type' => 'error', 'message' => "L'email n'existe pas"];
        }

        if (!password_verify($password, $user->getPassword())) {
            return ['ok' => false, 'type' => 'error', 'message' => "Le mot de passe ne correspond pas"];
        }

        return ['ok' => true, 'type' => 'success', 'message' => "Vous vous êtes bien connecté !"];
    }

    static public function register($email, $password): array {
        if (empty($email) || empty($password)) {
            return ['ok' => false, 'type' => 'error', 'message' => "Merci de renseigner vos informations"];
        }

        $user = (new UserModel())->getUserByEmail($email);

        if ($user) {
            return ['ok' => false, 'type' => 'error', 'message' => "L'email est déjà utilisé"];
        }

        return ['ok' => true, 'type' => 'success', 'message' => "Inscription réussie"];
    }
}

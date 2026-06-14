<?php

declare(strict_types=1);

/**
 * Validation des données de connexion et d'inscription.
 */
class UserValidator
{
    /**
     * Valide une tentative de connexion : champs présents, email existant,
     * mot de passe correct.
     *
     * @param string $email    Email saisi.
     * @param string $password Mot de passe en clair saisi.
     * @return array{ok: bool, type: string, message: string} Résultat de validation.
     */
    public static function login($email, $password): array
    {
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

    /**
     * Valide une inscription : champs présents et email pas déjà utilisé.
     *
     * @param string $email    Email saisi.
     * @param string $password Mot de passe en clair saisi.
     * @return array{ok: bool, type: string, message: string} Résultat de validation.
     */
    public static function register($email, $password): array
    {
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

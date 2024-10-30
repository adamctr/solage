<?php

class AuthMiddleware {
    public function handle() {
        $sessionController = new SessionController();

        // Vérifiez si l'utilisateur est connecté
        if (!$sessionController->isLoggedIn()) {
            // Redirigez vers la page de connexion
            header("Location: /login");
            exit;
        }
    }
}

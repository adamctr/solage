<?php

class AdminMiddleware {
    public function handle() {
        $session = new SessionController();

        if (!$session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!$session->isAdmin()) {
            Logger::get()->warning('admin.access.denied', [
                'user_id' => SessionController::getUserId(),
                'uri'     => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo "403 — accès réservé aux administrateurs.";
            exit;
        }
    }
}

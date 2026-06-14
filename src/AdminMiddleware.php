<?php

declare(strict_types=1);

class AdminMiddleware
{
    public function handle()
    {
        $session = new SessionManager(new UserModel());

        if (!$session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!$session->isAdmin()) {
            Logger::get()->warning('admin.access.denied', [
                'user_id' => $session->getUserId(),
                'uri'     => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo "403 — accès réservé aux administrateurs.";
            exit;
        }
    }
}

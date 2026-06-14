<?php

declare(strict_types=1);

class AuthMiddleware
{
    public function handle()
    {
        $session = new SessionManager(new UserModel());

        if (!$session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }
}

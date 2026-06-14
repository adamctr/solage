<?php

declare(strict_types=1);

class CsrfMiddleware
{
    public function handle()
    {
        // Le token arrive soit via le header X-CSRF-Token (requêtes fetch),
        // soit via le champ caché csrf_token (formulaires HTML classiques).
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';

        if (!CsrfHelper::verifyToken($token)) {
            Logger::get()->warning('csrf.token.rejected', [
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo "403 — Token CSRF refusé.";
            exit;
        }
    }
}

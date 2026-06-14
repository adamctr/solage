<?php

declare(strict_types=1);

class CsrfHelper
{
    public static function getToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyToken(?string $token): bool
    {
        return !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], (string) $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::getToken(), ENT_QUOTES) . '">';
    }
}

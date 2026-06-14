<?php

declare(strict_types=1);

/**
 *
 */
class Utils
{
    /**
     * Envoie une réponse JSON (encodée) au client.
     *
     * @param bool       $success Succès de l'opération.
     * @param string     $message Message à afficher côté client.
     * @param mixed|null $data    Données additionnelles éventuelles.
     * @return void
     */
    public static function sendResponse($success, $message, $data = null)
    {
        $response = ['success' => $success, 'message' => $message];

        if ($data) {
            $response['data'] = $data;
        }

        echo json_encode($response);
    }

    /**
     * Indique si la requête courante est une requête AJAX.
     *
     * @return bool
     */
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * HTML-escape user-controlled content for safe output in views.
     * Always use this when rendering anything that could come from user input.
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

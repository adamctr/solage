<?php

/**
 *
 */
class Utils {

    /**
     * Fonction utilitaire pour envoyer une réponse JSON
     */
    static function sendResponse($success, $message, $data = null)
    {
        $response = ['success' => $success, 'message' => $message];

        if ($data) {
            $response['data'] = $data;
        }

        echo json_encode($response);
    }

    public static function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * HTML-escape user-controlled content for safe output in views.
     * Always use this when rendering anything that could come from user input.
     */
    public static function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

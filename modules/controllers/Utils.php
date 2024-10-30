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
}

<?php

class LikeController {

    function create() {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        var_dump($data);

        if (!isset($data['post'])) {
            echo json_encode(['success' => false, 'message' => 'ID du post non reçue']);
            return;
        }

        try {
            $id = null;
            $user = 1; // SESSION_USER
            $created_at = date('Y-m-d H:i:s');
            $response = null;
            $post = $data['post'];

            $like = new LikeModel($id, $user, $post, $response, $created_at);
            $result = $like->create();
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => "Succès lors de la création du post",
                    'post' => [
                        'user' => $user,
                        'post' => $post,
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du like']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur de traitement : ' . $e->getMessage()]);
        }
    }


}

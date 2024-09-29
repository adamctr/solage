<?php
require_once __DIR__ . '/../models/PostModel.php';

class PostController {
    public function create() {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation des données
        if (!isset($data['user']) || !isset($data['content']) || empty(trim($data['content']))) {
            echo json_encode(['success' => false, 'message' => 'Données invalides ou contenu vide']);
            return;
        }

        try {
            $user = $data['user'];
            $content = $data['content'];
            $date = date('Y-m-d H:i:s');

            $post = new PostModel(null, $user, $content, $date, null, null);
            $result = $post->createPost();
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => "Succès lors de la création du post",
                    'post' => [
                        'id' => null,
                        'user' => $user,
                        'content' => $content,
                        'date' => $date,
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du post']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur de traitement : ' . $e->getMessage()]);
        }
    }
}

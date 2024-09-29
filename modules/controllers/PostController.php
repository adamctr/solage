<?php
class PostController {
    public function create() {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation des données
        if (!isset($data['user']) || !isset($data['content']) || empty(trim($data['content']))) {
            Utils::sendResponse(false, 'Données invalides ou contenu vide');
            return;
        }

        try {
            $user = $data['user'];
            $content = $data['content'];
            $date = date('Y-m-d H:i:s');

            $post = new PostModel(null, $user, $content, $date, null, null);
            $postId = $post->createPost(); // if post created, return postId
            if ($postId) {
                Utils::sendResponse(true, "Succès lors de la création du post", [
                    'id' => $postId,
                    'user' => $user,
                    'content' => $content,
                    'date' => $date,
                ]);
            } else {
                Utils::sendResponse(false, 'Erreur lors de la création du post');
            }
        } catch (Exception $e) {
            Utils::sendResponse(false, 'Erreur de traitement : ' . $e->getMessage());
        }
    }
}

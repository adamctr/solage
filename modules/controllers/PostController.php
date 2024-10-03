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

        // Si il y a un replyTo, alors l'insérer

        try {
            $user = $data['user'];
            $content = $data['content'];
            $date = date('Y-m-d H:i:s');
            $replyTo = (int) $data['replyTo'];

            $post = new PostModel(null, $user, $content, $date, null, $replyTo);
            $postId = $post->createPost($replyTo); // if post created, return postId
            if ($postId) {
                Utils::sendResponse(true, "Succès lors de la création du post", [
                    'id' => $postId,
                    'user' => $user,
                    'content' => $content,
                    'date' => $date,
                    'reply_to' => $replyTo,
                ]);
            } else {
                Utils::sendResponse(false, 'Erreur lors de la création du post');
            }
        } catch (Exception $e) {
            Utils::sendResponse(false, 'Erreur de traitement : ' . $e->getMessage());
        }
    }
}

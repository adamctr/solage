<?php

/**
 *
 */
class LikeController
{
    protected $like;

    /**
     * Méthode pour gérer la création ou la suppression d'un like
     * @return void
     */
    function create()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        // Vérification des données reçues
        if (!isset($data['post'])) {
            Utils::sendResponse(false, 'ID du post non reçue pour le like');
            return;
        }

        $user = SessionController::getUserId();
        $post = $data['post'];

        try {
            // Création de l'objet LikeModel
            $this->like = new LikeModel(null, $user, $post, null, date('Y-m-d H:i:s'));

            if ($this->like->likeAlreadyExist()) {
                $this->deleteLike();
            } else {
                $this->createLike();
            }
        } catch (Exception $e) {
            Utils::sendResponse(false, 'Erreur de traitement du like : ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour créer un like
     * @return void
     */
    protected function createLike()
    {
        try {
            $result = $this->like->create();
            if ($result) {
                Utils::sendResponse(true, 'Succès lors de la création du like', [
                    'user' => $this->like->getUser(),
                    'post' => $this->like->getPost(),
                    'id' => $this->like->getId(),
                ]);
            } else {
                Utils::sendResponse(false, 'Erreur lors de la création du like');
            }
        } catch (Exception $ex) {
            Utils::sendResponse(false, 'Erreur : ' . $ex->getMessage());
        }
    }

    /**
     * Méthode pour supprimer un like
     * @return void
     */
    protected function deleteLike()
    {
        try {
            $this->like->delete();
            Utils::sendResponse(true, 'Like bien supprimé');
        } catch (Exception $ex) {
            Utils::sendResponse(false, 'Erreur : ' . $ex->getMessage());
        }
    }
}

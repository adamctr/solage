<?php

declare(strict_types=1);

/**
 * Gère les likes via une seule action « toggle » (crée ou supprime).
 */
class LikeController
{
    protected $like;

    /**
     * Bascule le like de l'utilisateur sur un post : le crée s'il n'existe pas,
     * le supprime sinon.
     *
     * @return void
     */
    public function create()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        // Vérification des données reçues
        if (!isset($data['post'])) {
            Utils::sendResponse(false, 'ID du post non reçue pour le like');
            return;
        }

        $user = (new SessionManager(new UserModel()))->getUserId();
        $post = $data['post'];

        try {
            // Création de l'objet LikeModel
            $this->like = new LikeModel(null, $user, $post, date('Y-m-d H:i:s'));

            if ($this->like->likeAlreadyExist()) {
                $this->deleteLike();
            } else {
                $this->createLike();
            }
        } catch (Exception $e) {
            Logger::get()->error('like.toggle.failed', ['exception' => $e]);
            Utils::sendResponse(false, 'Erreur de traitement du like.');
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
            Logger::get()->error('like.create.failed', ['exception' => $ex]);
            Utils::sendResponse(false, 'Erreur lors de la création du like.');
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
            Logger::get()->error('like.delete.failed', ['exception' => $ex]);
            Utils::sendResponse(false, 'Erreur lors de la suppression du like.');
        }
    }
}

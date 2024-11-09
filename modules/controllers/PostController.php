<?php

/**
 *
 */
class PostController {
    /**
     * @return void
     */
    public function create() {
        header('Content-Type: application/json');

        $data = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode($_POST['data'], true);
            //var_dump($_FILES);

            // Vérifier si une image a été téléchargée
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Traitement de l'image
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileSize = $_FILES['image']['size'];
                $fileType = $_FILES['image']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Définir une liste d'extensions de fichiers autorisées
                $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];

                // Vérifier l'extension
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    // Chemin de destination pour enregistrer le fichier
                    $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . 'uploaded_files/';
                    $newFileName = uniqid() . '.' . $fileExtension; // Renommer le fichier
                    $dest_path = $uploadFileDir . basename($newFileName);

                    // Déplacer le fichier temporaire vers le dossier de destination
                    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                        Utils::sendResponse(false, 'Erreur lors de l\'enregistrement de l\'image.');
                        return;
                    }
                } else {
                    Utils::sendResponse(false, 'Type de fichier non autorisé.');
                    return;
                }
            }
        }
        // Validation des données
        if (!isset($data['content']) || empty(trim($data['content']))) {
            Utils::sendResponse(false, 'Données invalides ou contenu vide');
            return;
        }

        try {
            $sessionController = new SessionController();
            $user = $sessionController->getUserId();
            $username = $sessionController->getName();
            $userimage = $sessionController->getImage();
            $content = $data['content'];
            $date = date('Y-m-d H:i:s');
            $image = $newFileName ?? null;

            $replyTo = $data['replyTo'] !== 0 && $data['replyTo'] !== null ? (int) $data['replyTo'] : null;
            $replyToParent = $data['replyToParent'] !== 0 && $data['replyToParent'] !== null ? (int) $data['replyToParent'] : null;
            if ($replyToParent === null) {
                if ($replyTo !== null) {
                    $replyToParent = $replyTo;
                }
            }

            $post = new PostModel(null, $user, $content, $date, null, $replyTo, $image, $replyToParent);
            $postId = $post->createPost($replyTo, $replyToParent); // if post created, return postId
            if ($postId) {
                Utils::sendResponse(true, "Succès lors de la création du post", [
                    'id' => $postId,
                    'user' => $user,
                    'username' => $username,
                    'userimage' => $userimage,
                    'content' => $content,
                    'date' => $date,
                    'reply_to' => $replyTo,
                    'image' => $image,
                    'reply_to_parent' => $replyToParent,
                ]);
            } else {
                Utils::sendResponse(false, 'Erreur lors de la création du post');
            }
        } catch (Exception $e) {
            Utils::sendResponse(false, 'Erreur de traitement : ' . $e->getMessage());
        }
    }
}

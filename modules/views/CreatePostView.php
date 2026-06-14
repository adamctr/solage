<?php

declare(strict_types=1);

/**
 * Formulaire de création de post (publication ou réponse).
 */
class CreatePostView
{
    /**
     * Rend le formulaire de création de post. Si un post est fourni, le
     * formulaire répond à ce post.
     *
     * @param PostModel|null $post Post auquel répondre, ou null pour un nouveau post.
     * @return string HTML du formulaire.
     */
    public static function show($post = null)
    {
        $session = new SessionManager(new UserModel());
        $username = $session->getName();
        $userImage = $session->getImage();

        //$username = $sessionController->getProfilePicture();

        ob_start();
        ?>

        <div class="createPost">
            <div class="postAvatarContainer">
                <div class="postAvatar"><?= Utils::e($userImage) ?></div>
            </div>
            <div class="postInsideContainer">
                <div class="postNameDate">
                    <div class=""><?= Utils::e($username) ?></div>
                </div>
                <div class="postContentTools">
                    <span id="postContent" aria-label="Contenu du post" class="postCreateInput" role="textbox" contenteditable="true"></span>
                    <div id="postContentImageContainer"></div>
                    <div class="postCreateTools">
                        <div class="postCreateTool">
                            <label for="file-input">
                                <span class="sr-only">Upload image</span>
                                <?php echo file_get_contents('assets/image.svg'); ?>
                            </label>
                            <input id="file-input" accept="image/*" type="file" class="hidden" />
                            <button id="removeImageButton" class="hidden">Supprimer l'image</button>
                        </div>
                        <button id="postCreateButton" class="postCreateButton"
                            <?php if ($post) : ?>
                                data-postToReply="<?= $post->getId() ?>"

                                <?php if ($post->getReplyTo() !== null) : ?>
                                    data-postParent="<?= $post->getPostParentId() ?>"
                                <?php endif; ?>

                            <?php endif; ?>


                        >Publier</button>
                    </div>
                </div>
            </div>

        </div>
        <?php
        $content = ob_get_clean();
        return $content;
    }
}

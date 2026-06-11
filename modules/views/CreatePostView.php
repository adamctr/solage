<?php

class CreatePostView {
    /**
     * @param $post
     * @return false|string
     */
    static public function show($post = null) {
        $sessionController = new SessionController();
        $username = $sessionController->getName();
        $userImage = $sessionController->getImage();

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
                                <?php echo file_get_contents('assets/image.svg' ); ?>
                            </label>
                            <input id="file-input" accept="image/*" type="file" class="hidden" />
                            <button id="removeImageButton" class="hidden">Supprimer l'image</button>
                        </div>
                        <button id="postCreateButton" class="postCreateButton"
                            <?php if ($post): ?>
                                data-postToReply="<?= htmlspecialchars($post->getId(), ENT_QUOTES, 'UTF-8') ?>"

                                <?php if($post->getReplyTo() !== null ): ?>
                                    data-postParent="<?= htmlspecialchars($post->getPostParentId(), ENT_QUOTES, 'UTF-8') ?>"
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

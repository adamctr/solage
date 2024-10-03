<?php

class ResponseView {

    static public function show($post, $responses) {
        $mainPostView = new MainPostView($post);
        $postResponsesView = new PostResponsesView($responses);
        ob_start();
        ?>

        <?= $mainPostView->show(); ?>

        <div class="createPost">
            <div class="postAvatarContainer"><img class="postAvatar" src="https://pbs.twimg.com/profile_images/1834449929932062720/3j3_C2V5_400x400.jpg" alt=""></div>
            <div class="postInsideContainer">
                <div class="postContentTools">
                    <span id="postContent" class="textarea, postCreateInput" role="textbox" contenteditable="true"></span>
                    <div class="postCreateTools">
                        <div class="postCreateTool">
                            <label for="file-input">
                                <?php echo file_get_contents('assets/image.svg' ); ?>
                            </label>
                            <input id="file-input" accept="image/*" type="file" style="display: none;" />
                        </div>
                        <button id="postCreateButton" class="postCreateButton" data-postToReply="<?= $post->getId() ?>">Publier</button>
                    </div>
                </div>
            </div>
        </div>

        <?= $postResponsesView->show(); ?>

        <?php
        $responseView = ob_get_clean();
        (new LayoutView('Réponse à !!USER!!', 'Page du post et des réponses de l\'utilisateur', $responseView))->show();
    }
}

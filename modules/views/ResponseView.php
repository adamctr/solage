<?php

/**
 *
 */
class ResponseView {

    /**
     * @param $post
     * @param $responses
     * @return void
     */
    static public function show($post, $responses) {
        $mainPostView = new MainPostView($post);
        $postView = new PostView($responses);

        $usernameOfThePostOwner = UserModel::getNameFromId($post->getUserId());

        ob_start();
        ?>
        <div class="navigationContainer">
            <div class="navigationBtnContainer">
                <button class="navigationBtn" onclick="history.back()">
                    <?php echo file_get_contents('assets/back-arrow.svg' ); ?>
                </button>
            </div>
        </div>


        <?= $mainPostView->show(); ?>

        <?= CreatePostView::show($post) ?>

        <div id="postList">
            <?= $postView->show(); ?>
        </div>

        <?php
        $responseView = ob_get_clean();
        (new LayoutView('Réponse à ', 'Page du post et des réponses de l\'utilisateur', $responseView))->show();
    }
}

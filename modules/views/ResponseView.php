<?php

/**
 *
 */
class ResponseView {

    /**
     * @param $post
     * @param $responses
     * @param array $users  Map [user_id => UserModel] for the main post author and all reply authors.
     * @return void
     */
    static public function show($post, $responses, array $users) {
        $author = $users[$post->getUserId()] ?? null;

        $mainPostView = new MainPostView($post, $author);
        $postView = new PostView($responses, $users);

        $usernameOfThePostOwner = $author ? $author->getName() : '';

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
        (new LayoutView(('Réponse à ' . Utils::e($usernameOfThePostOwner)), 'Page du post et des réponses de l\'utilisateur', $responseView))->show();
    }
}

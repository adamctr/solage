<?php

declare(strict_types=1);

/**
 * Vue de la page d'un post : le post principal et ses réponses.
 */
class ResponseView
{
    /**
     * Affiche le post principal, le formulaire de réponse et les réponses.
     *
     * @param PostModel             $post      Post principal.
     * @param PostModel[]           $responses Réponses au post.
     * @param array<int, UserModel> $users     Auteurs (post + réponses) indexés par id.
     * @return void
     */
    public static function show($post, $responses, array $users)
    {
        $author = $users[$post->getUserId()] ?? null;

        $mainPostView = new MainPostView($post, $author);
        $postView = new PostView($responses, $users);

        $usernameOfThePostOwner = $author ? $author->getName() : '';

        ob_start();
        ?>
        <div class="navigationContainer">
            <div class="navigationBtnContainer">
                <button class="navigationBtn backButton" >
                    <?php echo file_get_contents('assets/back-arrow.svg'); ?>
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

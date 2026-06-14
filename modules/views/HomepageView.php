<?php

declare(strict_types=1);

/**
 * Vue du fil d'accueil : liste des posts récents.
 */
class HomepageView
{
    protected $posts;
    protected $users;

    /**
     * @param PostModel[]           $posts Posts à afficher.
     * @param array<int, UserModel> $users Auteurs indexés par identifiant.
     */
    public function __construct($posts, array $users)
    {
        $this->posts = $posts;
        $this->users = $users;
    }

    /**
     * Rend le fil (formulaire de création + liste de posts) dans le layout.
     *
     * @return void
     */
    public function show()
    {
        $postView = new PostView($this->posts, $this->users);
        ob_start();
        ?>

        <h1 class="homepageTitle">Votre actualité</h1>

        <?= CreatePostView::show(); ?>

        <div id="postList">
        <?= $postView->show(); ?>
        </div>
        <?php
        (new LayoutView('Votre actualité', "Consultez les récents posts de vos proches, ou l'actualité autour de vous", ob_get_clean()))->show();
    }
}

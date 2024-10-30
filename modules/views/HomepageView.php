<?php
class HomepageView {
    protected $posts;
    protected $sidebar;
    protected $rightSidebar;

    public function __construct($posts) {
        $this->posts = $posts;
    }

    /**
     * @return void
     */
    public function show() {
        $postView = new PostView($this->posts);
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

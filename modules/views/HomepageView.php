<?php
class HomepageView {
    protected $posts;

    public function __construct($posts) {
        $this->posts = $posts;
    }

    public function show() {
        $postView = new PostView($this->posts);
        ob_start();
        ?>

        <h1>Les posts</h1>
        <?= $postView->show(); ?>

        <?php
        (new LayoutView('La meilleure homepage', 'Ceci est la meilleure page', ob_get_clean()))->show();
    }
}

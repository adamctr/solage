<?php

//namespace Home\Controllers;
class HomepageController {
    protected $posts;

        public function __construct() {
            // Récupérer les posts avec leurs likes
            $postModel = new PostModel();
            $this->posts = $postModel->getPosts();
        }

    public function execute() {
        $view = new HomepageView($this->posts);
        $view->show();

    }
}

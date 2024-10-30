<?php

//namespace Home\Controllers;
class HomepageController {
    protected $posts;
    protected $sidebar;

    protected $rightSidebar;

    public function __construct() {
        $postModel = PostModel::getPosts();
        $this->posts = $postModel;
    }

    /**
     * @return void
     */
    public function execute() {
        $view = new HomepageView($this->posts);
        $view->show();
    }
}

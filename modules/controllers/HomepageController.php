<?php

class HomepageController {
    /**
     * @return void
     */
    public function execute() {
        $posts = PostModel::getPosts();
        $userIds = array_map(fn($p) => $p->getUserId(), $posts);
        $users = UserModel::getUsersByIds($userIds);

        $view = new HomepageView($posts, $users);
        $view->show();
    }
}

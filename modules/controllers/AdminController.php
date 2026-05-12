<?php

class AdminController {

    public function __construct() {

    }

    public function showAdminPage() {
        $adminView = new AdminView();
        $adminView->show();
    }

    public function showSearchUsersPage() {
        $query = $_GET['query'] ?? '';

        $searchModel = new SearchModel();
        $users = $searchModel->searchUsers($query);

        $adminView = new AdminView();
        $adminView->renderUsersResult($users);
    }

    public function showSearchPostsPage() {
        $query = $_GET['query'] ?? '';

        $searchModel = new SearchModel();
        $posts = $searchModel->searchPosts($query);

        $userIds = array_map(fn($p) => $p->getUserId(), $posts);
        $users = UserModel::getUsersByIds($userIds);

        $adminView = new AdminView();
        $adminView->renderPostsResult($posts, $users);
    }

}

<?php

declare(strict_types=1);

/**
 * Back-office d'administration : tableau de bord et recherches.
 */
class AdminController
{
    /**
     * Affiche le tableau de bord d'administration.
     *
     * @return void
     */
    public function showAdminPage()
    {
        $adminView = new AdminView();
        $adminView->show();
    }

    /**
     * Affiche les utilisateurs correspondant au terme recherché (GET « query »).
     *
     * @return void
     */
    public function showSearchUsersPage()
    {
        $query = $_GET['query'] ?? '';

        $searchModel = new SearchModel();
        $users = $searchModel->searchUsers($query);

        $adminView = new AdminView();
        $adminView->renderUsersResult($users);
    }

    /**
     * Affiche les posts correspondant au terme recherché (GET « query »).
     *
     * @return void
     */
    public function showSearchPostsPage()
    {
        $query = $_GET['query'] ?? '';

        $searchModel = new SearchModel();
        $posts = $searchModel->searchPosts($query);

        $userIds = array_map(fn($p) => $p->getUserId(), $posts);
        $users = UserModel::getUsersByIds($userIds);

        $currentUserId = (new SessionManager(new UserModel()))->getUserId();
        PostModel::attachLikedState($posts, $currentUserId);

        $adminView = new AdminView();
        $adminView->renderPostsResult($posts, $users);
    }
}

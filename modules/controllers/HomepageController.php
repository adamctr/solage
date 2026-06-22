<?php

declare(strict_types=1);

/**
 * Page d'accueil : affiche le fil des posts récents.
 */
class HomepageController
{
    /**
     * Affiche le fil d'accueil (posts récents et leurs auteurs).
     *
     * @return void
     */
    public function execute()
    {
        $posts = PostModel::getPosts();
        $userIds = array_map(fn($p) => $p->getUserId(), $posts);
        $users = UserModel::getUsersByIds($userIds);

        $currentUserId = (new SessionManager(new UserModel()))->getUserId();
        PostModel::attachLikedState($posts, $currentUserId);

        $view = new HomepageView($posts, $users);
        $view->show();
    }
}

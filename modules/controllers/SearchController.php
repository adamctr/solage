<?php

declare(strict_types=1);

/**
 * Recherche : page de recherche et affichage des résultats.
 */
class SearchController
{
    /**
     * Affiche la page de recherche (formulaire vide).
     *
     * @return void
     */
    public function showSearchPage()
    {
        $view = new SearchView();
        $view->render();
    }

    /**
     * Affiche les résultats de recherche pour le terme « query » (GET).
     *
     * @return void
     */
    public function searchResults()
    {
        $query = $_GET['query'] ?? '';

        $searchModel = new SearchModel();
        $results = $searchModel->search($query);
        $results = is_array($results) ? $results : [];

        $userIds = array_map(fn($p) => $p->getUserId(), $results);
        $users = UserModel::getUsersByIds($userIds);

        $currentUserId = (new SessionManager(new UserModel()))->getUserId();
        PostModel::attachLikedState($results, $currentUserId);

        $searchView = new SearchView();
        $searchView->renderResults($results, $users);
    }
}

<?php
class SearchController {
    public function showSearchPage() {
        $view = new SearchView();
        echo $view->render();
    }

    public function searchResults() {
        $query = $_GET['query'] ?? '';

        $searchModel = new SearchModel();
        $results = $searchModel->search($query);
        $results = is_array($results) ? $results : [];

        $userIds = array_map(fn($p) => $p->getUserId(), $results);
        $users = UserModel::getUsersByIds($userIds);

        $searchView = new SearchView();
        echo $searchView->renderResults($results, $users);
    }

}

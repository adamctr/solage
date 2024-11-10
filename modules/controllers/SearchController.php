<?php
class SearchController {
    public function showSearchPage() {
        $view = new SearchView();
        echo $view->render();
    }

    public function searchResults() {
        $query = $_GET['query'] ?? '';
        $type = $_GET['type'] ?? ''; // user ou post

        // Instanciation du modèle de recherche et recherche par nom d'utilisateur
        $searchModel = new SearchModel();
        $results = $searchModel->search($query);

        // Si $results est null, on le remplace par un tableau vide
        $results = is_array($results) ? $results : [];

        // Affichage des résultats avec la vue appropriée
        $searchView = new SearchView();
        echo $searchView->renderResults($results);
    }

}

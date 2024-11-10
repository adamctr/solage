<?php

class SearchView {
    public function render() {
        ob_start();
        ?>
        <div class="search-page">
            <h1>Recherche</h1>
            <form action="/search/results" method="GET">
                <input type="text" name="query" placeholder="Entrez votre recherche...">
                <button type="submit">Rechercher</button>
            </form>
        </div>
        <?php
        (new LayoutView('Recherche', 'Recherchez des posts', ob_get_clean()))->show();
    }

    public function renderResults($resultPosts) {
        ob_start();
        ?>
        <div class="search-page">
            <form action="/search/results" method="GET">
                <input type="text" name="query" placeholder="Entrez votre recherche...">
            </form>
        </div>
        <div class="search-results">
            <h2>Résultats de la recherche</h2>
            <?php if (!empty($resultPosts)): ?>
                <?php foreach ($resultPosts as $post):
                    // On passe chaque post individuellement à PostView
                    $postView = new PostView([$post]);
                    ?>
                    <?= $postView->show(); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        </div>
        <?php
        (new LayoutView('Résultats de votre recherche', 'Détails des posts trouvés', ob_get_clean()))->show();
    }
}

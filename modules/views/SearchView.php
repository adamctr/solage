<?php

declare(strict_types=1);

/**
 * Vue de recherche : formulaire et résultats.
 */
class SearchView
{
    /**
     * Rend la page de recherche (formulaire vide) dans le layout.
     *
     * @return void
     */
    public function render()
    {
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

    /**
     * Rend la page de résultats de recherche dans le layout.
     *
     * @param PostModel[]           $resultPosts Posts trouvés.
     * @param array<int, UserModel> $users       Auteurs indexés par identifiant.
     * @return void
     */
    public function renderResults($resultPosts, array $users)
    {
        ob_start();
        ?>
        <div class="search-page">
            <form action="/search/results" method="GET">
                <input type="text" name="query" placeholder="Entrez votre recherche...">
            </form>
        </div>
        <div class="search-results">
            <h2>Résultats de la recherche</h2>
            <?php if (!empty($resultPosts)) : ?>
                <?php foreach ($resultPosts as $post) :
                    $postView = new PostView([$post], $users);
                    ?>
                    <?= $postView->show(); ?>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        </div>
        <?php
        (new LayoutView('Résultats de votre recherche', 'Détails des posts trouvés', ob_get_clean()))->show();
    }
}

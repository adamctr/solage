<?php

declare(strict_types=1);

/**
 * Vues du back-office : tableau de bord et résultats de recherche.
 */
class AdminView
{
    /**
     * Affiche le tableau de bord d'administration dans le layout.
     *
     * @return void
     */
    public static function show()
    {
        ob_start();
        ?>
        <?= self::adminSearch() ?>
        <?php
        (new LayoutView('Admin', 'Administrez les utilisateurs ou message', ob_get_clean()))->show();
    }

    /**
     * Rend les deux formulaires de recherche (utilisateurs et posts).
     *
     * @return string HTML des formulaires de recherche.
     */
    public static function adminSearch()
    {
        ob_start();
        ?>
        <div class="admin-page">
            <h1>Recherche</h1>
            <form action="/admin/search/results/users" method="GET">
                <label>Rechercher un utilisateur<input type="text" name="query" placeholder="Entrez votre recherche..."></label>
                <button type="submit">Rechercher</button>
            </form>

            <form action="/admin/search/results/posts" method="GET">
                <label for="">Rechercher un post<input type="text" name="query" placeholder="Entrez votre recherche..."></label>
                <button type="submit">Rechercher</button>
            </form>

        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Affiche les résultats de recherche de posts (avec action de suppression).
     *
     * @param PostModel[]           $resultPosts Posts trouvés.
     * @param array<int, UserModel> $users       Auteurs indexés par identifiant.
     * @return void
     */
    public function renderPostsResult($resultPosts, array $users)
    {
        ob_start();
        ?>

        <?= self::adminSearch() ?>

        <div class="search-results">
            <h2>Résultats</h2>
            <?php if (!empty($resultPosts)) : ?>
                <?php foreach ($resultPosts as $post) :
                    $postView = new PostView([$post], $users);
                    ?>
                    <?= $postView->showAdminPost(); ?>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        </div>
        <?php
        (new LayoutView('Résultats de votre recherche', 'Détails des posts trouvés', ob_get_clean()))->show();
    }

    /**
     * Affiche les résultats de recherche d'utilisateurs (avec suppression).
     *
     * @param UserModel[] $resultUsers Utilisateurs trouvés.
     * @return void
     */
    public function renderUsersResult($resultUsers)
    {
        ob_start();
        ?>
        <?= self::adminSearch() ?>

        <div class="search-results">
            <h2>Résultats</h2>
            <?php if (!empty($resultUsers)) : ?>
                <?php foreach ($resultUsers as $user) : ?>
                    <div class="user fade-in" id="user-<?= $user->getId() ?>">
                        <div class="postAvatarContainer">
                            <div class="postAvatar"><?= Utils::e($user->getImage()) ?></div>
                        </div>
                        <div><?= Utils::e($user->getName()) ?></div>
                        <div class="userViewButton">
                            <form action="/api/users/delete" data-id="<?= $user->getId() ?>" class="deleteForm" method="post">
                                <button type="submit">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else : ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        </div>

        <?php
        (new LayoutView('Résultats de votre recherche', 'Détails des utilisateurs trouvés', ob_get_clean()))->show();
    }
}

<?php

class AdminView {
    static public function show() {
        ob_start();
        ?>
        <?= self::adminSearch() ?>
        <?php
        (new LayoutView('Admin', 'Administrez les utilisateurs ou message', ob_get_clean()))->show();
    }

    static public function adminSearch() {
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

    public function renderPostsResult($resultPosts) {
        ob_start();
        ?>

        <?= self::adminSearch() ?>

        <div class="search-results">
            <h2>Résultats</h2>
            <?php if (!empty($resultPosts)): ?>
                <?php foreach ($resultPosts as $post):
                    // On passe chaque post individuellement à PostView
                    $postView = new PostView([$post]);
                    ?>
                    <?= $postView->showAdminPost(); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        </div>
        <?php
        (new LayoutView('Résultats de votre recherche', 'Détails des posts trouvés', ob_get_clean()))->show();
    }

    public function renderUsersResult($resultUsers) {
        ob_start();
        ?>
        <?= self::adminSearch() ?>

        <div class="search-results">
            <h2>Résultats</h2>
            <?php if (!empty($resultUsers)): ?>
                <?php foreach ($resultUsers as $user): ?>
                    <div class="user fade-in" id="user-<?= $user->getId() ?>">
                        <div class="postAvatarContainer">
                            <div class="postAvatar"><?= $user->getImage() ?></div>
                        </div>
                        <div><?= $user->getName() ?></div>
                        <div class="userViewButton">
                            <form action="/api/users/delete" data-id="<?= $user->getId() ?>" class="deleteForm" method="post">
                                <button type="submit">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

        <?php else: ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        </div>

        <?php
        (new LayoutView('Résultats de votre recherche', 'Détails des utilisateurs trouvés', ob_get_clean()))->show();
    }
}

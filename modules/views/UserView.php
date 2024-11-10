<?php
class UserView {
    protected $user; // Propriété pour stocker les données de l'utilisateur

    // Le constructeur prend en paramètre l'objet utilisateur récupéré par le contrôleur
    public function __construct($user = null) {
        if ($user != null) {
            $this->user = $user;
        }
    }

    // Méthode pour afficher la page HTML
    public function show() {
        $userPosts = PostModel::getAllPostsByUserId($this->user->getId());
        ob_start();
        ?>

        <div class="user-profile">
            <h1>Profil de <?= htmlspecialchars($this->user->getName() ?? '') ?></h1>

            <div class="user-header">
                <div class="postAvatarContainer">
                    <div class="postAvatar"><?= $this->user->getImage() ?></div>
                </div>

                <?php if($this->user->getId() == SessionController::getUserId()): ?>
                <form action="/edituser/<?= $this->user->getId() ?>" method="get">
                    <input type="submit" value="Modifier le profil"/>
                </form>
                <form action="/logout" method="post">
                    <button type="submit" class="logoutButton">Déconnexion</button>
                </form>
                <?php endif; ?>
            </div>

            <div class="user-posts">
                <h2>Posts récents</h2>
                <?php
                $postView = new PostView($userPosts);
                echo $postView->show();
                ?>
            </div>

        </div>

        <?php
        // Envoi du HTML capturé au layout global
        (new LayoutView('Profil de ' . htmlspecialchars($this->user->getName() ?? ''), 'Détails du profil utilisateur', ob_get_clean()))->show();
    }

    public function showLoginForm() {
        ob_start()
        ?>

        <form id="loginForm" class="loginRegisterForm" action="/login" method="POST">
            <h2>Se connecter</h2>
            <div id="messageContainer"></div>

            <div class="formBlock">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="formBlock">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Connexion</button>

        </form>

        <?php
        $content = ob_get_clean();
        (new LoginRegisterLayoutView('Connexion', 'Page de connexion du site', $content))->show();

    }

    public function showRegisterForm() {
        ob_start()
        ?>
        <form id="registerForm" class="loginRegisterForm" action="/register" method="POST">
            <h2>S'inscrire</h2>
            <div id="messageContainer"></div>

            <div class="formBlock">
                <label for="name">Nom d'utilisateur :</label>
                <input type="text" id="name" name="name" required autocomplete="username">
            </div>

            <div class="formBlock">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>

            <div class="formBlock">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>

            <button type="submit">Inscription</button>
        </form>


        <?php
        $content = ob_get_clean();
        (new LoginRegisterLayoutView('Inscription', "Page d'inscription", $content))->show();

    }
}

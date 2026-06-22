<?php

declare(strict_types=1);

/**
 * Vue du profil utilisateur et des formulaires de connexion/inscription.
 */
class UserView
{
    protected $user;
    protected $posts;
    protected $users;

    /**
     * @param UserModel|null        $user  Utilisateur affiché (null pour les formulaires).
     * @param PostModel[]           $posts Posts de l'utilisateur.
     * @param array<int, UserModel> $users Auteurs indexés par identifiant.
     */
    public function __construct($user = null, array $posts = [], array $users = [])
    {
        $this->user = $user;
        $this->posts = $posts;
        $this->users = $users;
    }

    /**
     * Affiche le profil de l'utilisateur (en-tête + posts) dans le layout.
     *
     * @return void
     */
    public function show()
    {
        $sessionUserId = (new SessionManager(new UserModel()))->getUserId();
        ob_start();
        ?>

        <div class="user-profile">
            <h1>Profil de <?= Utils::e($this->user->getName()) ?></h1>

            <div class="user-header">
                <div class="postAvatarContainer">
                    <div class="postAvatar"><?= Utils::e($this->user->getImage()) ?></div>
                </div>

                <?php if ($this->user->getId() == $sessionUserId) : ?>
                <form action="/edituser/<?= $this->user->getId() ?>" method="get">
                    <input type="submit" value="Modifier le profil"/>
                </form>
                <form action="/logout" method="post">
                    <?= CsrfHelper::field() ?>
                    <button type="submit" class="logoutButton">Déconnexion</button>
                </form>
                <?php endif; ?>
            </div>

            <div class="user-posts">
                <h2>Posts récents</h2>
                <?php
                $postView = new PostView($this->posts, $this->users);
                echo $postView->show();
                ?>
            </div>

        </div>

        <?php
        (new LayoutView('Profil de ' . Utils::e($this->user->getName()), 'Détails du profil utilisateur', ob_get_clean()))->show();
    }

    /**
     * Affiche le formulaire de connexion dans le layout connexion/inscription.
     *
     * @return void
     */
    public function showLoginForm()
    {
        ob_start()
        ?>

        <form id="loginForm" class="loginRegisterForm" action="/login" method="POST">
            <?= CsrfHelper::field() ?>
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

            <p class="formSwitch">Pas encore de compte ? <a href="/register">S'inscrire</a></p>

        </form>

        <?php
        $content = ob_get_clean();
        (new LoginRegisterLayoutView('Connexion', 'Page de connexion du site', $content))->show();
    }

    /**
     * Affiche le formulaire d'inscription dans le layout connexion/inscription.
     *
     * @return void
     */
    public function showRegisterForm()
    {
        ob_start()
        ?>
        <form id="registerForm" class="loginRegisterForm" action="/register" method="POST">
            <?= CsrfHelper::field() ?>
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

            <p class="formSwitch">Déjà un compte ? <a href="/login">Se connecter</a></p>
        </form>


        <?php
        $content = ob_get_clean();
        (new LoginRegisterLayoutView('Inscription', "Page d'inscription", $content))->show();
    }
}

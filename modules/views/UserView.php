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
        if ($this->user === null) {
            echo "Utilisateur non trouvé."; // Affichage d'un message d'erreur
            return;
        }
        // Utilisation d'un buffer de sortie pour capturer le HTML généré
        ob_start();
        ?>

        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport"
                  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <meta name="description" content="<?=$this->description?>">
            <title><?= $this->user->getName() ?></title>
        </head>
        <body>
            <h1>Profil de <?= htmlspecialchars($this->user->getName()); ?></h1>
            <p>Email : <?= htmlspecialchars($this->user->getEmail()); ?></p>
        </body>
        

        <?php
        // Envoi du HTML capturé au layout global
        (new LayoutView('Profil de ' . htmlspecialchars($this->user->getName()), 'Détails du profil utilisateur', ob_get_clean()))->show();
    }

    public function showLoginForm() {
        ob_start()
        ?>
        <form class="loginRegisterForm" action="/login" method="POST">
            <h2>Se connecter</h2>

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
        <form class="loginRegisterForm" action="/register" method="POST">
            <h2>S'inscrire</h2>

            <div class="formBlock">
                <label for="name">Nom d'utilisateur :</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="formBlock">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="formBlock">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Inscription</button>
        </form>

        <?php
        $content = ob_get_clean();
        (new LoginRegisterLayoutView('Inscription', "Page d'inscription", $content))->show();

    }
}

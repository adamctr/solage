<?php
class UserView {
    protected $user; // Propriété pour stocker les données de l'utilisateur

    // Le constructeur prend en paramètre l'objet utilisateur récupéré par le contrôleur
    public function __construct($user) {
        $this->user = $user;
    }

    // Méthode pour afficher la page HTML
    public function show() {
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
            <title><?= $this->user->getFirstname() ?></title>
        </head>
        <body>
            <h1>Profil de <?= htmlspecialchars($this->user->getFirstname()); ?></h1>
            <p>Email : <?= htmlspecialchars($this->user->getEmail()); ?></p>
        </body>
        

        <?php
        // Envoi du HTML capturé au layout global
        (new LayoutView('Profil de ' . htmlspecialchars($this->user->getFirstname()), 'Détails du profil utilisateur', ob_get_clean()))->show();
    }
}

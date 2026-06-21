<?php

declare(strict_types=1);

/**
 * Gabarit HTML des pages de connexion et d'inscription.
 */
class LoginRegisterLayoutView
{
    /**
     * @param string $title       Titre de la page.
     * @param string $description Méta-description.
     * @param string $content     Contenu HTML déjà rendu à injecter.
     */
    public function __construct(private $title, private $description, private $content)
    {
    }

    /**
     * Rend la page complète (connexion/inscription) autour du contenu.
     *
     * @return void
     */
    public function show()
    {
        $config = Config::getInstance();
        $cssPath = $config->getCssPath();
        $jsPath = $config->getJsPath();
        ?>
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <meta name="description" content="<?=$this->description?>">
            <meta name="csrf-token" content="<?= CsrfHelper::getToken() ?>">
            <link href="/<?=$cssPath?>" rel="stylesheet" />
            <link rel="shortcut icon" href="/assets/favicon.ico" type="image/x-icon"/>
            <title><?= $this->title ?></title>
        </head>
        <body>
        <div class="loginRegisterContainer">
            <h1>Votre réseau social préféré</h1>
            <div class="homepagelogo">
                <?php echo file_get_contents('assets/y.svg'); ?>
            </div>


            <?= $this->content ?>
        </div>
        <div id="scrollTopBtn" class="hidden">
            <?php echo file_get_contents('assets/up-arrow.svg'); ?>
        </div>

        <script src="/<?=$jsPath?>"></script>
        <script src="/scripts/dynamicMessages.js"></script>

        </body>
        </html>
        <?php
    }
}

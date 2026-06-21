<?php

declare(strict_types=1);

/**
 * Gabarit HTML de page : en-tête, barres latérales et contenu injecté.
 */
class LayoutView
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
     * Rend la page complète (doctype, head, barres latérales, contenu).
     *
     * @return void
     */
    public function show()
    {

        $sidebar = new SidebarView();
        $rightSidebar = new RightSidebarView();

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
                <main class="main">
                    <?= $sidebar->show(); ?>
                    <div class="postContainer">
                        <?= $this->content; ?>
                    </div>
                    <?= $rightSidebar->show(); ?>
                </main>
                <div id="scrollTopBtn" class="hidden">
                    <?php echo file_get_contents('assets/up-arrow.svg'); ?>
                </div>

                <script src="/<?=$jsPath?>"></script>
            </body>
        </html>
        <?php
    }
}

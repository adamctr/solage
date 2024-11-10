<?php

class LoginRegisterLayoutView {
    public function __construct(private $title, private $description, private $content) {}

    /**
     * @return void
     */
    public function show() {
        $config = Config::getInstance();
        $cssPath = $config->getCssPath();
        $jsPath = $config->getJsPath();
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <meta name="description" content="<?=$this->description?>">
            <link href="/<?=$cssPath?>" rel="stylesheet" />
            <link rel="shortcut icon" href="/assets/yfavicon.ico" type="image/x-icon"/>
            <title><?= $this->title ?></title>
        </head>
        <body>
        <div class="loginRegisterContainer">
            <h1>Votre réseau social préféré</h1>
            <div class="homepagelogo">
                <?php echo file_get_contents('assets/y.svg' ); ?>
            </div>


            <?= $this->content ?>
        </div>
        <div id="scrollTopBtn" style="display: none;">
            <?php echo file_get_contents('assets/up-arrow.svg' ); ?>
        </div>

        <script src="/<?=$jsPath?>"></script>
        <script src="/scripts/dynamicMessages.js"></script>

        </body>
        </html>
        <?php
    }
}

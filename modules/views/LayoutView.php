<?php

class LayoutView {
    public function __construct(private $title, private $description, private $content) {
    }

    /**
     * @return void
     */
    public function show() {

        $sidebar = new SidebarView();
        $rightSidebar = new RightSidebarView();

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
                <main class="main">
                    <?= $sidebar->show(); ?>
                    <div class="postContainer">
                        <?= $this->content; ?>
                    </div>
                    <?= $rightSidebar->show(); ?>
                </main>
                <div id="scrollTopBtn" style="display: none;">
                    <?php echo file_get_contents('assets/up-arrow.svg' ); ?>
                </div>

                <script src="/<?=$jsPath?>"></script>
            </body>
        </html>
<?php
    }
}

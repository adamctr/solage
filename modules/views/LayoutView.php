<?php

class LayoutView {
    public function __construct(private $title, private $description, private $content) {}
    public function show() {

        $sidebar = new SidebarView();
        $rightSidebar = new RightSidebarView();
        ?>
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport"
                      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <meta name="description" content="<?=$this->description?>">
                <link href="/style/style.css" rel="stylesheet" />
                <link href="/style/design.css" rel="stylesheet" />
                <title><?= $this->title ?></title>
            </head>
            <body>
                <div class="main">
                    <?= $sidebar->show(); ?>
                    <div class="postContainer">
                        <?= $this->content; ?>
                    </div>
                    <?= $rightSidebar->show(); ?>
                </div>
            </body>
            <script src="/scripts/index.js"></script>

        </html>
<?php
    }
}

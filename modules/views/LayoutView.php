<?php

class LayoutView {
    public function __construct(private $title, private $description, private $content) {}
    public function show() {
        ?>
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport"
                      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <meta name="description" content="<?=$this->description?>">
                <title><?= $this->title ?></title>
            </head>
            <body>
                <?= $this->content; ?>
            </body>
            </html>
        <?php
    }
}

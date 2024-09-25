<?php

class PostView {
    protected $posts;
    public function __construct($posts)
    {
        $this->posts = $posts;
    }

    public function show() {
        ob_start();
        ?>;

        <h1>Les posts</h1>
        <?php foreach ($this->posts as $post) { ?>
            <h3><?= $post->getContent() ?></h3>
            <div><?= $post->getDate() ?></div> <?php
        }
        $postsHTML = ob_get_clean();
        return $postsHTML;
    }

    public function getContent() {
        var_dump($this->posts);
    }
}

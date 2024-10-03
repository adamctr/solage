<?php

class MainPostView {

    protected $post;

    function __construct($post) {
        $this->post = $post;
    }
    function show() {
        ob_start();
        ?>

        <div class="mainPost post" data-id="<?= $this->post->getId() ?>">

            <div class="postAvatarContainer"><img class="postAvatar" src="https://pbs.twimg.com/profile_images/1834449929932062720/3j3_C2V5_400x400.jpg" alt=""></div>
            <div class="postInsideContainer">
                <div class="postNameDate">
                    <div><?= $this->post->getUserId() ?></div>
                    <div class="postDate"><?= $this->post->getDate() ?></div>
                </div>
                <div class="postContentTools">
                    <div class="postContent"><?= $this->post->getContent() ?></div>
                    <div class="postTools">
                        <div class="postTool response">
                            <?= PostToolResponseView::show($this->post, 1); ?>
                        </div>
                        <?= PostToolHeartView::show($this->post, 1); ?>
                    </div>
                </div>
            </div>

        </div>

        <?php
        $content = ob_get_clean();
        return $content;
    }
}

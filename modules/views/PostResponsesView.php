<?php

class PostResponsesView {
    protected $posts;
    function __construct($posts) {
        $this->posts = $posts;
    }

    /**
     * @return string
     */
    public function show() {
                ob_start();
                ?>
                <div id="postList">
                <?php foreach ($this->posts as $post) {;?>
                    <div class="post fade-in" data-id="<?= $post->getId() ?>">

                        <div class="postAvatarContainer"><img class="postAvatar" src="https://pbs.twimg.com/profile_images/1834449929932062720/3j3_C2V5_400x400.jpg" alt=""></div>
                        <div class="postInsideContainer">
                            <div class="postNameDate">
                                <div><?= $post->getUserId() ?></div>
                                <div class="postDate"><?= $post->getDate() ?></div>
                            </div>
                            <div class="postContentTools">
                                <div class="postContent"><?= $post->getContent() ?></div>
                                <div class="postTools">
                                    <div class="postTool response">
                                        <?= PostToolResponseView::show($post, 1); ?>
                                    </div>
                                    <?= PostToolHeartView::show($post, 1); ?>
                                </div>
                        </div>
                    </div>
                </div>
                <?php
                }
                $postsHTML = ob_get_clean();
                return $postsHTML;
            }
}

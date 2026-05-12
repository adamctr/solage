<?php

class MainPostView {

    protected $post;
    protected $user;

    function __construct($post, $user) {
        $this->post = $post;
        $this->user = $user;
    }

    /**
     * @return string
     */
    function show() {
        ob_start();
        ?>

        <div class="mainPost post" data-id="<?= $this->post->getId() ?>">

            <div class="postAvatarContainer">
                <div class="postAvatar"><?= Utils::e($this->user?->getImage()) ?></div>
            </div>
            <div class="postInsideContainer">
                <div class="postNameDate">
                    <div><?= Utils::e($this->user?->getName()) ?></div>
                </div>
                <div class="postContentTools">
                    <div class="postContent"><?= Utils::e($this->post->getContent()) ?></div>

                    <?php if($this->post->getImagePath()): ?>
                    <img src="/uploaded_files/<?= Utils::e($this->post->getImagePath()) ?>" alt="Post Image" class="postImage" />
                    <?php endif; ?>

                    <div class="postDate"><?= $this->post->getDate() ?></div>
                    <div class="postTools">
                        <div class="postTool response">
                            <?= PostToolResponseView::show($this->post); ?>
                        </div>
                        <?= PostToolHeartView::show($this->post, SessionController::getUserId()); ?>
                    </div>
                </div>
            </div>

        </div>

        <?php
        return ob_get_clean();
    }
}

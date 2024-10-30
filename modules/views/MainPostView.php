<?php

class MainPostView {

    protected $post;

    function __construct($post) {
        $this->post = $post;
    }

    /**
     * @return string
     */
    function show() {
        ob_start();
        $user = new UserModel();
        $user = $user->getUserById($this->post->getUserId());
        ?>

        <div class="mainPost post" data-id="<?= $this->post->getId() ?>">

            <div class="postAvatarContainer">
                <div class="postAvatar"><?= $user->getImage() ?></div>
            </div>
            <div class="postInsideContainer">
                <div class="postNameDate">
                    <div><?= $user->getName() ?></div>
                </div>
                <div class="postContentTools">
                    <div class="postContent"><?= $this->post->getContent() ?></div>

                    <?php if($this->post->getImagePath()): ?>
                    <img src="/uploaded_files/<?= $this->post->getImagePath() ?>" alt="Post Image" class="postImage" />
                    <?php endif; ?>

                    <div class="postDate"><?= $this->post->getDate() ?></div>
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

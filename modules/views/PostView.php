<?php

class PostView {
    protected $posts;
    public function __construct($posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return string
     */
    public function show() {

        ob_start();
        ?>
        <?php foreach ($this->posts as $post) {
            $user = new UserModel();
            $user = $user->getUserById($post->getUserId());
            ?>
            <div class="post fade-in" data-id="<?= $post->getId() ?>">

                    <div class="postAvatarContainer">
                        <div class="postAvatar"><?= $user->getImage() ?></div>
                    </div>
                    <div class="postInsideContainer">
                        <div class="postNameDate">
                            <div><?= $user->getName() ?></div>
                            <div class="postDate"><?= $post->getDate() ?></div>
                        </div>
                            <div class="postContent"><p class="fitWidth"><?= $post->getContent() ?></p></div>

                        <?php if($post->getImagePath()): ?>
                            <img src="/uploaded_files/<?= $post->getImagePath() ?>" alt="" class="postImage" />
                        <?php endif; ?>

                        <div class="postContentTools">
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

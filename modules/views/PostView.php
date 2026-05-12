<?php

class PostView {
    protected $posts;
    protected $users;

    public function __construct($posts, array $users) {
        $this->posts = $posts;
        $this->users = $users;
    }

    /**
     * @return string
     */
    public function show() {
        ob_start();
        ?>
        <?php foreach ($this->posts as $post):
            $user = $this->users[$post->getUserId()] ?? null;
            ?>
            <div class="post fade-in" data-id="<?= $post->getId() ?>">

                    <div class="postAvatarContainer">
                        <div class="postAvatar"><?= Utils::e($user?->getImage()) ?></div>
                    </div>
                    <div class="postInsideContainer">
                        <div class="postNameDate">
                            <div><?= Utils::e($user?->getName()) ?></div>
                            <div class="postDate"><?= $post->getDate() ?></div>
                        </div>
                            <div class="postContent"><p class="fitWidth"><?= Utils::e($post->getContent()) ?></p></div>

                        <?php if($post->getImagePath()): ?>
                            <img src="/uploaded_files/<?= Utils::e($post->getImagePath()) ?>" alt="" class="postImage" />
                        <?php endif; ?>

                        <div class="postContentTools">
                            <div class="postTools">
                                <div class="postTool response">
                                    <?= PostToolResponseView::show($post); ?>
                                </div>
                                <?= PostToolHeartView::show($post, SessionController::getUserId()); ?>
                            </div>
                        </div>
                    </div>
            </div>

        <?php endforeach;
        return ob_get_clean();
    }


    public function showAdminPost() {
        ob_start();
        ?>
        <?php foreach ($this->posts as $post):
            $user = $this->users[$post->getUserId()] ?? null;
            ?>
            <div class="post fade-in" id="post-<?= $post->getId() ?>">

                <div class="postAvatarContainer">
                    <div class="postAvatar"><?= Utils::e($user?->getImage()) ?></div>
                </div>
                <div class="postInsideContainer">
                    <div class="postNameDate">
                        <div><?= Utils::e($user?->getName()) ?></div>
                        <div class="postDate"><?= $post->getDate() ?></div>
                    </div>
                    <div class="postContent"><p class="fitWidth"><?= Utils::e($post->getContent()) ?></p></div>

                    <?php if($post->getImagePath()): ?>
                        <img src="/uploaded_files/<?= Utils::e($post->getImagePath()) ?>" alt="" class="postImage" />
                    <?php endif; ?>

                    <div class="postContentTools">
                        <div class="postTools">
                            <div class="postTool response">
                                <form action="/api/posts/delete" data-id="<?= $post->getId() ?>" class="deletePostForm" method="post">
                                    <button type="submit">Supprimer</button>
                                </form>
                            </div>
                            <?= PostToolHeartView::show($post, SessionController::getUserId()); ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endforeach;
        return ob_get_clean();
    }
}

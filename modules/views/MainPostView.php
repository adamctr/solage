<?php

declare(strict_types=1);

/**
 * Vue d'un post « principal » (en-tête de la page d'un post).
 */
class MainPostView
{
    protected $post;
    protected $user;

    /**
     * @param PostModel      $post Post à afficher.
     * @param UserModel|null $user Auteur du post.
     */
    public function __construct($post, $user)
    {
        $this->post = $post;
        $this->user = $user;
    }

    /**
     * Rend le HTML du post principal.
     *
     * @return string HTML du post.
     */
    public function show()
    {
        $sessionUserId = (new SessionManager(new UserModel()))->getUserId();
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

                    <?php if ($this->post->getImagePath()) : ?>
                    <img src="/uploaded_files/<?= Utils::e($this->post->getImagePath()) ?>" alt="Post Image" class="postImage" />
                    <?php endif; ?>

                    <div class="postDate"><?= $this->post->getDate() ?></div>
                    <div class="postTools">
                        <div class="postTool response">
                            <?= PostToolResponseView::show($this->post); ?>
                        </div>
                        <?= PostToolHeartView::show($this->post, $sessionUserId); ?>
                    </div>
                </div>
            </div>

        </div>

        <?php
        return ob_get_clean();
    }
}

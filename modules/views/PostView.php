<?php

class PostView {
    protected $posts;
    public function __construct($posts)
    {
        $this->posts = $posts;
    }

    public function show() {
        ob_start();
        ?>
        <?php foreach ($this->posts as $post) { ?>
            <div class="post" data-id="<?= $post->getId() ?>">

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
                                    <?php echo file_get_contents('assets/response.svg' ); ?>
                                    <span class="menuTxt">Recherche</span></a>
                                    <?= $post->getResponses() ?></div>
                                <div class="postTool like">
                                    <?php echo file_get_contents('assets/heart.svg' ); ?>
                                    <?= $post->getLikes() ?></div>
                            </div>
                        </div>
                    </div>

            </div>

            <?php
        }
        $postsHTML = ob_get_clean();
        return $postsHTML;
    }

    public function getContent() {
        //var_dump($this->posts);
    }

}

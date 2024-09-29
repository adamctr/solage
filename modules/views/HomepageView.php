<?php
class HomepageView {
    protected $posts;
    protected $sidebar;
    protected $rightSidebar;

    public function __construct($posts) {
        $this->posts = $posts;
    }

    public function show() {
        $postView = new PostView($this->posts);
        ob_start();
        ?>

        <h1 class="homepageTitle">Votre actualité</h1>

        <div class="post createPost">
            <div class="postAvatarContainer"><img class="postAvatar" src="https://pbs.twimg.com/profile_images/1834449929932062720/3j3_C2V5_400x400.jpg" alt=""></div>
            <div class="postInsideContainer">
                <div class="postNameDate">
                    <div class="">name</div>
                    <div class="postDate">qzdqzd></div>
                </div>
                <div class="postContentTools">
                    <span id="postContent" class="textarea, postCreateInput" role="textbox" contenteditable="true"></span>
                    <div class="postCreateTools">
                        <div class="postCreateTool">
                            <label for="file-input">
                                <?php echo file_get_contents('assets/image.svg' ); ?>
                            </label>
                            <input id="file-input" type="file" style="display: none;" />
                        </div>
                        <button id="postCreateButton" class="postCreateButton">Publier</button>
                    </div>
                </div>
            </div>

        </div>
        <div id="postList">
        <?= $postView->show(); ?>
        </div>
        <?php
        (new LayoutView('La meilleure homepage', 'Ceci est la meilleure page', ob_get_clean()))->show();
    }
}

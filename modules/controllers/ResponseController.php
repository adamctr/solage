<?php

/**
 *
 */
class ResponseController {

    protected $postId;
    function __construct($postId) {
        $this->postId = $postId;
    }

    /**
     * @return void
     */
    function execute() {
        $postId = (int) $this->postId;
        $post = PostModel::getPostById($postId);
        $responses = $post->getResponses();

        $userIds = [$post->getUserId()];
        foreach ($responses as $r) {
            $userIds[] = $r->getUserId();
        }
        $users = UserModel::getUsersByIds($userIds);

        ResponseView::show($post, $responses, $users);
    }
}

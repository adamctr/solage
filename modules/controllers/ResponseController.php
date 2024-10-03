<?php

class ResponseController {

    protected $postId;
    function __construct($postId) {
        $this->postId = $postId;
    }

    function execute() {
        $postId = (int) $this->postId;

        $post = PostModel::getPostById($postId);
        $responses = $post->getResponses();

        $responseView = new ResponseView;
        $responseView->show($post ,$responses);
    }
}

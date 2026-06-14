<?php

declare(strict_types=1);

/**
 * Page d'un post : affiche le post ciblé et ses réponses.
 */
class ResponseController
{
    protected $postId;

    /**
     * @param int|string $postId Identifiant du post (paramètre d'URL).
     */
    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    /**
     * Affiche le post ciblé et ses réponses.
     *
     * @return void
     */
    public function execute()
    {
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

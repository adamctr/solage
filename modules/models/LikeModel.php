<?php

class LikeModel {
    protected $id;
    protected $user;
    protected $post;
    protected $response;
    protected $created_at;
    protected $db;

    function __construct($id, $user, $post, $response, $created_at) {
        $this->db = Database::getConnection();
        $this->id = $id;
        $this->user = $user;
        $this->post = $post;
        $this->response = $response;
        $this->created_at = $created_at;
    }

    /**
     * @return false|int
     */
    function create() {
        try {
            $statement = $this->db->prepare('INSERT INTO likes (user_id, post, response, created_at) VALUES (:user_id, :post, :response, :created_at)');
            $statement->bindValue(':user_id', $this->user);
            $statement->bindValue(':post', $this->post);
            $statement->bindValue(':response', $this->response);
            $statement->bindValue(':created_at', $this->created_at);
            $statement->execute();

            $this->id = $this->db->lastInsertId('likes_id_seq');
            return $this->id;
        } catch (PDOException $e) {
            Logger::get()->error('like.create.failed', [
                'user_id' => $this->user,
                'post_id' => $this->post,
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * @return bool
     */
    function delete() {
        try {
            $statement = $this->db->prepare('DELETE FROM likes WHERE user_id = :user_id AND post = :post');
            $statement->bindValue(':user_id', $this->user);
            $statement->bindValue(':post', $this->post);
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Logger::get()->error('like.delete.failed', [
                'user_id' => $this->user,
                'post_id' => $this->post,
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * @return bool
     */
    function likeAlreadyExist() {
        try {
            $statement = $this->db->prepare('SELECT * FROM likes WHERE user_id = :user_id AND post = :post LIMIT 1');
            $statement->bindValue(':user_id', $this->user);
            $statement->bindValue(':post', $this->post);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!$result === false) {
                return true;

            } else return false;
        } catch (PDOException $e) {
            Logger::get()->warning('like.exists.check_failed', [
                'user_id' => $this->user,
                'post_id' => $this->post,
                'exception' => $e,
            ]);
            return false;
        }

    }

    /**
     * @return int
     */
    function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    function getUser() {
        return $this->user;
    }

    /**
     * @return mixed
     */
    function getPost() {
        return $this->post;
    }

    /**
     * @return mixed
     */
    function getResponse() {
        return $this->response;
    }

    /**
     * @return dateTime
     */
    function getCreatedAt() {
        return $this->created_at;
    }
}

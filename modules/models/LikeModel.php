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

    function create() {
        try {
            $statement = $this->db->prepare('INSERT INTO likes (id, user, post, response, created_at) VALUES (:id, :user, :post, :response, :created_at)');
            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':user', $this->user);
            $statement->bindValue(':post', $this->post);
            $statement->bindValue(':response', $this->response);
            $statement->bindValue(':created_at', $this->created_at);
            $statement->execute();

            $this->id = $this->db->lastInsertId();
            return $this->id;
        } catch (PDOException $e) {
            var_dump('Erreur lors de l\'insertion du like la base de données : ' . $e->getMessage());
            return false;
        }
    }

    function delete() {
        try {
            $statement = $this->db->prepare('DELETE FROM likes WHERE user = :user AND post = :post');
            $statement->bindValue(':user', $this->user);
            $statement->bindValue(':post', $this->post);
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            var_dump('Erreur lors de la supression des données dans la base de données : ' . $e->getMessage());
            return false;
        }
    }

    function likeAlreadyExist() {
        try {
            $statement = $this->db->prepare('SELECT * FROM likes WHERE user = :user AND post = :post LIMIT 1');
            $statement->bindValue(':user', $this->user);
            $statement->bindValue(':post', $this->post);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!$result === false) {
                return true;

            } else return false;
        } catch (PDOException $e) {
            //var_dump('Erreur lors du check du like : ' . $e->getMessage());
            return false;
        }

    }

    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getPost() {
        return $this->post;
    }

    function getResponse() {
        return $this->response;
    }

    function getCreatedAt() {
        return $this->created_at;
    }
}

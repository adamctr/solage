<?php
class PostModel {
    protected $db;
    protected $user;
    protected $id;
    protected $content;
    protected $date;
    protected $likes;
    protected $responses;


    public function __construct($id, $user, $content, $date, $likes, $responses) {
        $this->db = DataBase::getConnection();
        $this->id = $id;
        $this->user = $user;
        $this->content = $content;
        $this->date = $date;
        $this->likes = $likes;
        $this->responses = $responses;
    }

    // Récupérer les posts avec le nombre de likes
    static public function getPosts(): array {
        $statement = DataBase::getConnection()->query(query: '
            SELECT p.id, p.user, p.content, p.date, 
                   COUNT(DISTINCT l.post) AS likes, 
                   COUNT(DISTINCT r.id) AS responses
            FROM posts p
            LEFT JOIN likes l ON p.id = l.post
            LEFT JOIN responses r ON p.id = r.post
            GROUP BY p.id
            ORDER BY p.date DESC LIMIT 5
        ');

        $posts = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $post = new PostModel($row->id, $row->user, $row->content, $row->date, $row->likes, $row->responses);
            $posts[] = $post;
        }
        return $posts;
    }

    public function createPost() {
        try {
            $statement = $this->db->prepare('INSERT INTO posts (user, content, date) VALUES (:user, :content, :date)');
            $statement->bindValue(':user', $this->user);
            $statement->bindValue(':content', $this->content);
            $statement->bindValue(':date', $this->date);
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            var_dump('Erreur lors de l\'insertion dans la base de données : ' . $e->getMessage());
            return false;
        }
    }

    public function getId(): int {
        return $this->id;
    }
    public function getUserId(): int {
        return $this->user;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getLikes(): int {
        return $this->likes;
    }

    public function getResponses(): int {
        return $this->responses;
    }
}

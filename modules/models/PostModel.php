<?php
class PostModel {
    protected $db;
    protected $id;
    protected $content;
    protected $date;
    protected $likes;

    public function __construct() {
        $this->db = DataBase::getConnection();
    }

    // Récupérer les posts avec le nombre de likes
    public function getPosts(): array {
        $statement = $this->db->query('
            SELECT p.id, p.content, p.date, COUNT(l.post) AS likes
            FROM posts p
            LEFT JOIN likes l ON p.id = l.post
            GROUP BY p.id
            ORDER BY p.date DESC LIMIT 5
        ');

        $posts = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $post = new PostModel();
            $post->__constructWithData($row->id, $row->content, $row->date, $row->likes);
            $posts[] = $post;
        }
        return $posts;

    }

    public function __constructWithData($id, $content, $date, $likes) {
        $this->id = $id;
        $this->content = $content;
        $this->date = $date;
        $this->likes = $likes;
    }

    public function getId(): int {
        return $this->id;
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
}

<?php
class PostModel {
    protected $db;
    protected $user;
    protected $id;
    protected $content;
    protected $date;
    protected $likes;
    protected $replyTo;


    public function __construct($id, $user, $content, $date, $likes, $replyTo) {
        $this->db = DataBase::getConnection();
        $this->id = $id;
        $this->user = $user;
        $this->content = $content;
        $this->date = $date;
        $this->likes = $likes;
        $this->replyTo = $replyTo;
    }

    // Récupérer les posts avec le nombre de likes
    static public function getPosts(): array {
        $statement = DataBase::getConnection()->query('
            SELECT p.id, p.user, p.content, p.date, 
                   COUNT(DISTINCT l.post) AS likes, 
                   p.reply_to
            FROM posts p
            LEFT JOIN likes l ON p.id = l.post
            WHERE p.reply_to IS NULL
            GROUP BY p.id
            ORDER BY p.date DESC
            LIMIT 5;
        ');

        $posts = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $post = new PostModel($row->id, $row->user, $row->content, $row->date, $row->likes, $row->reply_to);
            $posts[] = $post;
        }
        return $posts;
    }


    static public function getPostById(int $id): ?PostModel {
        $statement = DataBase::getConnection()->prepare('
        SELECT p.id, p.user, p.content, p.date, 
               COUNT(DISTINCT l.post) AS likes, 
               p.reply_to
        FROM posts p
        LEFT JOIN likes l ON p.id = l.post
        WHERE p.id = :id
        GROUP BY p.id
    ');

        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_OBJ);
        if ($row) {
            return new PostModel($row->id, $row->user, $row->content, $row->date, $row->likes, $row->reply_to);
        }
        return null;
    }


    public function createPost(?int $replyTo = null) {
        try {
            $statement = $this->db->prepare('INSERT INTO posts (user, content, date, reply_to) VALUES (:user, :content, :date, :reply_to)');
            $statement->bindValue(':user', $this->user);
            $statement->bindValue(':content', $this->content);
            $statement->bindValue(':date', $this->date);
            $statement->bindValue(':reply_to', $replyTo, PDO::PARAM_INT); // Null par défaut si pas de réponse
            $statement->execute();

            $this->id = $this->db->lastInsertId();
            return $this->id;
        } catch (PDOException $e) {
            var_dump('Erreur lors de l\'insertion du post dans la base de données : ' . $e->getMessage());
            return false;
        }
    }

    public function getResponses(): array {
        // Préparer la requête pour récupérer les réponses du post
        $statement = $this->db->prepare('
        SELECT r.id, r.content, r.user, r.date
        FROM posts p
        JOIN posts r ON r.reply_to = p.id
        WHERE p.id = :post_id
        ORDER BY r.date ASC
    ');

        // Lier l'identifiant du post
        $statement->bindParam(':post_id', $this->id, PDO::PARAM_INT);
        $statement->execute();

        // Récupérer les réponses sous forme d'objets PostModel
        $responses = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $response = new PostModel($row->id, $row->user, $row->content, $row->date, 0, 0); // 0 pour likes et responses car ce sont des réponses
            $responses[] = $response;
        }
        return $responses;
    }

    public function getLikesCount(): int {
        // Préparer la requête pour récupérer le nombre de likes du post
        $statement = $this->db->prepare('
        SELECT COUNT(*) AS likes_count
        FROM likes
        WHERE post = :post_id
    ');

        // Lier l'identifiant du post
        $statement->bindParam(':post_id', $this->id, PDO::PARAM_INT);
        $statement->execute();

        // Récupérer le résultat
        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->likes_count : 0; // Retourne 0 si aucun like n'existe
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

    public function getResponsesCount(): int {
        // Préparer la requête pour compter les réponses liées au post
        $statement = $this->db->prepare('
        SELECT COUNT(*) AS response_count
        FROM posts
        WHERE reply_to = :post_id
    ');

        // Lier l'identifiant du post
        $statement->bindParam(':post_id', $this->id, PDO::PARAM_INT);
        $statement->execute();

        // Récupérer le résultat
        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->response_count : 0; // Retourner 0 si aucune réponse n'est trouvée
    }
}

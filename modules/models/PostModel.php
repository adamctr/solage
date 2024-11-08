<?php
class PostModel {
    protected $db;
    protected $user;
    protected $id;
    protected $content;
    protected $date;
    protected $likes;
    protected $replyTo;
    protected $image;
    protected $reply_to_parent;



    public function __construct($id, $user, $content, $date, $likes, $replyTo, $image, $reply_to_parent) {
        $this->db = DataBase::getConnection();
        $this->id = $id;
        $this->user = $user;
        $this->content = $content;
        $this->date = $date;
        $this->likes = $likes;
        $this->replyTo = $replyTo;
        $this->image = $image;
        $this->reply_to_parent = $reply_to_parent;
    }

    /**
     * @return array
     */
    static public function getPosts(): array {
        $statement = DataBase::getConnection()->query('
            SELECT p.id, p.user, p.content, p.date, 
            COUNT(DISTINCT l.post) AS likes, 
            p.reply_to, p.image, p.reply_to_parent
            FROM posts p
            LEFT JOIN likes l ON p.id = l.post
            WHERE p.reply_to IS NULL
            GROUP BY p.id, p.user, p.content, p.date, p.reply_to, p.image, p.reply_to_parent
            ORDER BY p.date DESC
            LIMIT 20;
           ');

        $posts = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $post = new PostModel($row->id, $row->user, $row->content, $row->date, $row->likes, $row->reply_to, $row->image, $row->reply_to_parent);
            $posts[] = $post;
        }
        return $posts;
    }


    /**
     * @param int $id post
     * @return PostModel|null
     */
    static public function getPostById(int $id): ?PostModel {
        $statement = DataBase::getConnection()->prepare('
        SELECT p.id, p.user, p.content, p.date, 
               COUNT(DISTINCT l.post) AS likes, 
               p.reply_to, p.image, p.reply_to_parent
        FROM posts p
        LEFT JOIN likes l ON p.id = l.post
        WHERE p.id = :id
        GROUP BY p.id
    ');

        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_OBJ);
        if ($row) {
            return new PostModel($row->id, $row->user, $row->content, $row->date, $row->likes, $row->reply_to, $row->image, $row->reply_to_parent);
        }
        return null;
    }


    /**
     * @param int|null $replyTo = id post
     * @return false|string
     */
    public function createPost(?int $replyTo = null, ?int $replyToParent = null) {
        try {
            $statement = $this->db->prepare('INSERT INTO posts (user, content, date, reply_to, image, reply_to_parent) VALUES (:user, :content, :date, :reply_to, :image, :reply_to_parent)');
            $statement->bindValue(':user', $this->user);
            $statement->bindValue(':content', $this->content);
            $statement->bindValue(':date', $this->date);
            $statement->bindValue(':reply_to', $replyTo, PDO::PARAM_INT); // Null par défaut si pas de réponse
            $statement->bindValue(':image', $this->image);
            $statement->bindValue(':reply_to_parent', $replyToParent, PDO::PARAM_INT);
            $statement->execute();

            $this->id = $this->db->lastInsertId();
            return $this->id;
        } catch (PDOException $e) {
            var_dump('Erreur lors de l\'insertion du post dans la base de données : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @return array
     */
    public function getResponses(): array {
        // Préparer la requête pour récupérer les réponses du post
        $statement = $this->db->prepare('
        SELECT r.id, r.content, r.user, r.date, r.likes, r.reply_to, r.image, r.reply_to_parent
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
            $response = new PostModel($row->id, $row->user, $row->content, $row->date, 0, 0, !empty($row->image) ? $row->image : null, $row->reply_to_parent); // 0 pour likes et responses car ce sont des réponses
            $responses[] = $response;
        }
        return $responses;
    }

    /**
     * @return int
     */
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

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getDate(): string {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getLikes(): int {
        return $this->likes;
    }

    /**
     * @return mixed
     */
    public function getImagePath() {
        return $this->image;
    }

    /**
     * @return int
     */
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

    public function getPostParentId(): int {
        return $this->reply_to_parent;
    }
}

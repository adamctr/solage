<?php

declare(strict_types=1);

/**
 * Modèle d'un post (publication ou réponse) et de ses accès en base.
 */
class PostModel
{
    protected $db;
    protected $user;
    protected $id;
    protected $content;
    protected $date;
    protected $likes;
    protected $replyTo;
    protected $image;
    protected $reply_to_parent;
    protected $liked = false;



    /**
     * @param int|null    $id              Identifiant du post (null à la création).
     * @param int         $user            Identifiant de l'auteur.
     * @param string      $content         Contenu textuel du post.
     * @param string      $date            Date de publication (format SQL).
     * @param int|null    $likes           Nombre de likes (null si non chargé).
     * @param int|null    $replyTo         Identifiant du post auquel celui-ci répond, ou null.
     * @param string|null $image           Nom de fichier de l'image jointe, ou null.
     * @param int|null    $reply_to_parent Identifiant du post racine du fil, ou null.
     */
    public function __construct($id, $user, $content, $date, $likes, $replyTo, $image, $reply_to_parent)
    {
        $this->db = Database::getConnection();
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
     * Récupère les 20 posts racines les plus récents avec leur nombre de likes.
     *
     * @return PostModel[] Liste de posts (les plus récents d'abord).
     */
    public static function getPosts(): array
    {
        $statement = Database::getConnection()->query('
            SELECT p.id, p.user_id, p.content, p.date, 
            COUNT(DISTINCT l.post) AS likes, 
            p.reply_to, p.image, p.reply_to_parent
            FROM posts p
            LEFT JOIN likes l ON p.id = l.post
            WHERE p.reply_to IS NULL
            GROUP BY p.id, p.user_id, p.content, p.date, p.reply_to, p.image, p.reply_to_parent
            ORDER BY p.date DESC
            LIMIT 20;
           ');

        $posts = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $post = new PostModel(
                $row->id,
                $row->user_id,
                $row->content,
                $row->date,
                $row->likes,
                $row->reply_to,
                $row->image,
                $row->reply_to_parent
            );
            $posts[] = $post;
        }
        return $posts;
    }


    /**
     * Récupère un post par son identifiant, avec son nombre de likes.
     *
     * @param int $id Identifiant du post.
     * @return PostModel|null Le post, ou null s'il n'existe pas.
     */
    public static function getPostById(int $id): ?PostModel
    {
        $statement = Database::getConnection()->prepare('
        SELECT p.id, p.user_id, p.content, p.date, 
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
            return new PostModel(
                $row->id,
                $row->user_id,
                $row->content,
                $row->date,
                $row->likes,
                $row->reply_to,
                $row->image,
                $row->reply_to_parent
            );
        }
        return null;
    }

    /**
     * Récupère tous les posts d'un utilisateur, les plus récents d'abord.
     *
     * @param int $userId Identifiant de l'auteur.
     * @return PostModel[] Posts de l'utilisateur.
     */
    public static function getAllPostsByUserId(int $userId): array
    {
        $statement = Database::getConnection()->prepare('
        SELECT p.id, p.user_id, p.content, p.date, 
               COUNT(DISTINCT l.post) AS likes, 
               p.reply_to, p.image, p.reply_to_parent
        FROM posts p
        LEFT JOIN likes l ON p.id = l.post
        WHERE p.user_id = :userId
        GROUP BY p.id
        ORDER BY p.date DESC
    ');

        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->execute();

        $posts = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $posts[] = new PostModel(
                $row->id,
                $row->user_id,
                $row->content,
                $row->date,
                $row->likes,
                $row->reply_to,
                $row->image,
                $row->reply_to_parent
            );
        }

        return $posts;
    }



    /**
     * Insère le post en base et renvoie son identifiant.
     *
     * @param int|null $replyTo       Identifiant du post auquel on répond, ou null.
     * @param int|null $replyToParent Identifiant du post racine du fil, ou null.
     * @return string|false Identifiant du post créé, ou false en cas d'erreur.
     */
    public function createPost(?int $replyTo = null, ?int $replyToParent = null)
    {
        try {
            $statement = $this->db->prepare(
                'INSERT INTO posts (user_id, content, date, reply_to, image, reply_to_parent)
                 VALUES (:user_id, :content, :date, :reply_to, :image, :reply_to_parent)'
            );
            $statement->bindValue(':user_id', $this->user);
            $statement->bindValue(':content', $this->content);
            $statement->bindValue(':date', $this->date);
            $statement->bindValue(':reply_to', $replyTo, PDO::PARAM_INT); // Null par défaut si pas de réponse
            $statement->bindValue(':image', $this->image);
            $statement->bindValue(':reply_to_parent', $replyToParent, PDO::PARAM_INT);
            $statement->execute();

            $this->id = $this->db->lastInsertId('posts_id_seq');
            return $this->id;
        } catch (PDOException $e) {
            Logger::get()->error('post.create.failed', [
                'user_id' => $this->user,
                'reply_to' => $replyTo,
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Récupère les réponses directes à ce post.
     *
     * @return PostModel[] Réponses (les plus récentes d'abord).
     */
    public function getResponses(): array
    {
        // Préparer la requête pour récupérer les réponses du post
        $statement = $this->db->prepare('
        SELECT r.id, r.content, r.user_id, r.date, r.likes, r.reply_to, r.image, r.reply_to_parent
        FROM posts p
        JOIN posts r ON r.reply_to = p.id
        WHERE p.id = :post_id
        ORDER BY r.date DESC
    ');

        // Lier l'identifiant du post
        $statement->bindParam(':post_id', $this->id, PDO::PARAM_INT);
        $statement->execute();

        // Récupérer les réponses sous forme d'objets PostModel
        $responses = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            // 0 pour les likes/réponses car ce sont elles-mêmes des réponses
            $response = new PostModel(
                $row->id,
                $row->user_id,
                $row->content,
                $row->date,
                0,
                0,
                !empty($row->image) ? $row->image : null,
                $row->reply_to_parent
            );
            $responses[] = $response;
        }
        return $responses;
    }

    /**
     * Compte les likes du post en base.
     *
     * @return int Nombre de likes.
     */
    public function getLikesCount(): int
    {
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
     * Supprime un post par son identifiant.
     *
     * @param int $id Identifiant du post.
     * @return bool true si un post a été supprimé, false sinon ou en cas d'erreur.
     */
    public static function delete(int $id): bool
    {
        try {
            // Connexion à la base de données
            $db = Database::getConnection();

            // Supprimer le post lui-même
            $statement = $db->prepare('
            DELETE FROM posts WHERE id = :post_id
        ');

            // Lier l'ID du post à la requête
            $statement->bindParam(':post_id', $id, PDO::PARAM_INT);
            $statement->execute();

            // Vérification si le post a bien été supprimé
            if ($statement->rowCount() > 0) {
                return true;  // Retourne true si la suppression a réussi
            } else {
                return false;  // Retourne false si aucun post n'a été supprimé
            }
        } catch (PDOException $e) {
            Logger::get()->error('post.delete.failed', [
                'post_id' => $id,
                'exception' => $e,
            ]);
            return false;
        }
    }



    /**
     * @return int Identifiant du post.
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return int Identifiant de l'auteur.
     */
    public function getUserId(): int
    {
        return (int) $this->user;
    }

    /**
     * @return string Contenu textuel du post.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string Date de publication (format SQL).
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return int Nombre de likes (tel que chargé avec le post).
     */
    public function getLikes(): int
    {
        return (int) $this->likes;
    }

    /**
     * @return string|null Nom de fichier de l'image jointe, ou null.
     */
    public function getImagePath()
    {
        return $this->image;
    }

    /**
     * Compte les réponses directes à ce post.
     *
     * @return int Nombre de réponses directes.
     */
    public function getResponsesCount(): int
    {
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

    /**
     * @return int|null Identifiant du post racine du fil, ou null.
     */
    public function getPostParentId(): ?int
    {
        return $this->reply_to_parent === null ? null : (int) $this->reply_to_parent;
    }

    /**
     * @return int|null Identifiant du post auquel celui-ci répond, ou null.
     */
    public function getReplyTo(): ?int
    {
        return $this->replyTo === null ? null : (int) $this->replyTo;
    }

    /**
     * Indique si le post est liké par l'utilisateur courant.
     * État transitoire renseigné par le contrôleur via attachLikedState().
     *
     * @return bool
     */
    public function isLiked(): bool
    {
        return $this->liked;
    }

    /**
     * Renseigne l'état « liké » du post.
     *
     * @param bool $liked
     * @return void
     */
    public function setLiked(bool $liked): void
    {
        $this->liked = $liked;
    }

    /**
     * Marque en une seule requête les posts likés par l'utilisateur courant,
     * pour éviter une requête par bouton cœur dans les vues de liste (N+1).
     *
     * @param PostModel[]     $posts  Posts affichés.
     * @param int|string|null $userId Identifiant de l'utilisateur courant.
     * @return void
     */
    public static function attachLikedState(array $posts, $userId): void
    {
        $postIds = array_map(fn($post) => $post->getId(), $posts);
        $likedPostIds = LikeModel::getLikedPostIds($userId, $postIds);
        foreach ($posts as $post) {
            $post->setLiked(in_array($post->getId(), $likedPostIds, true));
        }
    }
}

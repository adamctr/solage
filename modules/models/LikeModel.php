<?php

declare(strict_types=1);

/**
 * Accès aux « likes » : un like associe un utilisateur à un post.
 */
class LikeModel
{
    protected $id;
    protected $user;
    protected $post;
    protected $created_at;
    protected $db;

    /**
     * @param int|null    $id         Identifiant du like (null tant qu'il n'est pas enregistré).
     * @param int         $user       Identifiant de l'utilisateur qui like.
     * @param int         $post       Identifiant du post liké.
     * @param string|null $created_at Date de création (format SQL), ou null.
     */
    public function __construct($id, $user, $post, $created_at)
    {
        $this->db = Database::getConnection();
        $this->id = $id;
        $this->user = $user;
        $this->post = $post;
        $this->created_at = $created_at;
    }

    /**
     * Enregistre le like en base.
     *
     * @return int|false Identifiant du like créé, ou false en cas d'erreur.
     */
    public function create()
    {
        try {
            $statement = $this->db->prepare(
                'INSERT INTO likes (user_id, post, created_at)
                 VALUES (:user_id, :post, :created_at)'
            );
            $statement->bindValue(':user_id', $this->user);
            $statement->bindValue(':post', $this->post);
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
     * Supprime le like (couple utilisateur/post) de la base.
     *
     * @return bool true si la requête s'est exécutée, false en cas d'erreur.
     */
    public function delete()
    {
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
     * Indique si l'utilisateur a déjà liké ce post.
     *
     * @return bool
     */
    public function likeAlreadyExist()
    {
        try {
            $statement = $this->db->prepare('SELECT * FROM likes WHERE user_id = :user_id AND post = :post LIMIT 1');
            $statement->bindValue(':user_id', $this->user);
            $statement->bindValue(':post', $this->post);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!$result === false) {
                return true;
            } else {
                return false;
            }
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
     * Récupère, parmi un ensemble de posts, ceux likés par un utilisateur.
     * Requête groupée pour éviter un N+1 lors du rendu des boutons cœur.
     *
     * @param int|string|null $userId  Identifiant de l'utilisateur courant.
     * @param int[]           $postIds Identifiants des posts affichés.
     * @return int[] Identifiants des posts likés par l'utilisateur.
     */
    public static function getLikedPostIds($userId, array $postIds): array
    {
        if ($userId === null || empty($postIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $statement = Database::getConnection()->prepare(
            "SELECT post FROM likes WHERE user_id = ? AND post IN ($placeholders)"
        );
        $statement->execute(array_merge([$userId], array_values($postIds)));

        $likedPostIds = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $likedPostIds[] = (int) $row->post;
        }
        return $likedPostIds;
    }

    /**
     * @return int|null Identifiant du like.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int Identifiant de l'utilisateur.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int Identifiant du post liké.
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return string|null Date de création (format SQL).
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}

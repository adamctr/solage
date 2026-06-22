<?php

declare(strict_types=1);

/**
 * Modèle d'un utilisateur et de ses accès en base.
 */
class UserModel
{
    protected $db;
    protected $id;
    protected $name;
    protected $email;
    protected $password;
    protected $role;
    protected $image;

    /**
     * Hydrate le modèle depuis une ligne SQL (objet), ou crée une instance vide
     * servant de fabrique (ex. pour appeler getUserById).
     *
     * @param object|null $userData Ligne utilisateur (PDO FETCH_OBJ), ou null.
     */
    public function __construct($userData = null)
    {
        $this->db = Database::getConnection();
        if ($userData) {
            $this->id = $userData->id;
            $this->name = $userData->name;
            $this->email = $userData->email;
            $this->password = $userData->password;
            $this->role = $userData->role;
            $this->image = $userData->image;
        }
    }

    /**
     * Récupère un utilisateur par son identifiant.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return UserModel|null L'utilisateur, ou null s'il n'existe pas.
     */
    public function getUserById($userId)
    {
        $statement = $this->db->prepare('SELECT id, name, email, password, role, image FROM users WHERE id = :id');
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row ? new UserModel($row) : null;
    }

    /**
     * @return int Identifiant de l'utilisateur.
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return string Nom d'affichage.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string Adresse email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string Hash du mot de passe.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string|null Identifiant du rôle (FK roles.id) en chaîne, ou null.
     */
    public function getRole(): ?string
    {
        // La colonne role est un entier (FK vers roles.id) ; on respecte le
        // type de retour ?string déclaré, en mode strict.
        return $this->role === null ? null : (string) $this->role;
    }

    /**
     * Résout le libellé du rôle (table roles) à partir de l'id de rôle.
     *
     * @return string|null Nom du rôle (ex. « Admin »), ou null si sans rôle.
     */
    public function getRoleName(): ?string
    {
        if ($this->role === null) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT name FROM roles WHERE id = :id');
        $stmt->execute([':id' => $this->role]);
        $name = $stmt->fetchColumn();
        return $name === false ? null : $name;
    }

    /**
     * @return string|null Emoji/avatar de l'utilisateur, ou null.
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Récupère le nom d'un utilisateur à partir de son identifiant.
     *
     * @param int $id Identifiant de l'utilisateur.
     * @return string Nom de l'utilisateur.
     */
    public static function getNameFromId($id)
    {
        $statement = Database::getConnection()->prepare('SELECT name FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row->name;
    }

    /**
     * Bulk-fetch users by their IDs to avoid N+1 in list views.
     * Returns a map keyed by user id.
     *
     * @param int[] $ids
     * @return array<int, UserModel>
     */
    public static function getUsersByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getConnection()->prepare(
            "SELECT id, name, email, password, role, image FROM users WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $users[(int)$row->id] = new UserModel($row);
        }
        return $users;
    }

    /**
     * Récupère un utilisateur par son email.
     *
     * @param string $email Adresse email recherchée.
     * @return UserModel|null L'utilisateur, ou null s'il n'existe pas.
     */
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ? new UserModel($row) : null;
    }

    /**
     * Crée un utilisateur (mot de passe hashé, avatar emoji aléatoire).
     *
     * @param string $name     Nom d'affichage.
     * @param string $email    Adresse email.
     * @param string $password Mot de passe en clair (hashé avant insertion).
     * @return bool true si l'insertion a réussi.
     */
    public function createUser($name, $email, $password)
    {
        include 'assets/emojiList.php';
        $randomEmoji = $emojiList[array_rand($emojiList)];
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (name, email, password, image) VALUES (:name, :email, :password, :image)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':image', $randomEmoji, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Met à jour le nom d'un utilisateur, et son mot de passe uniquement si un
     * nouveau est fourni (un mot de passe vide signifie « conserver l'actuel »).
     *
     * @param int    $userId   Identifiant de l'utilisateur.
     * @param string $name     Nouveau nom d'affichage.
     * @param string $password Nouveau mot de passe en clair, ou chaîne vide pour conserver l'actuel.
     * @return bool true si la mise à jour a réussi.
     */
    public function updateUser($userId, $name, $password)
    {
        if ($password === null || $password === '') {
            $sql = "UPDATE users SET name = :name WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET name = :name, password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Supprime un utilisateur par son identifiant.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return bool true si la suppression a réussi.
     */
    public function deleteUser($userId)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);


        return $stmt->execute();
    }
}

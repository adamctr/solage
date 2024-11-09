<?php
class UserModel {
    protected $db;
    protected $id;
    protected $name;
    protected $email;
    protected $password;
    protected $role;
    protected $image;

    public function __construct($userData = null) {
        $this->db = DataBase::getConnection();
        if ($userData) {
            $this->id = $userData->id;
            $this->name = $userData->name;
            $this->email = $userData->email;
            $this->password = $userData->password;
            $this->role = $userData->role;
            $this->image = $userData->image;
        }
    }

    public function getUserById($userId) {
        $statement = $this->db->prepare('SELECT id, name, email, password, role, image FROM users WHERE id = :id');
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row ? new UserModel($row) : null;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getImage() {
        return $this->image;
    }

    static public function getNameFromId($id) {
        $statement = DataBase::getConnection()->prepare('SELECT name FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row->name;
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ? new UserModel($row) : null;
    }

    public function createUser($name, $email, $password) {
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
}

<?php
class UserModel
{ // définir les données du modèle
    protected $db;
    protected $id;
    protected $name;
    protected $firstname;
    protected $email;
    protected $password;
    protected $role;
    protected $image;

    public function __construct() {
        $this->db = DataBase::getConnection();
    }

    // Récupérer données des utilisateurs
    public function getUserById($userId)
    {
        $statement = $this->db->prepare('SELECT id, name, firstname, email, password, role, image FROM users WHERE id = :id');
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch(PDO::FETCH_OBJ);

        if ($row) {
            // Créer un objet UserModel avec les données récupérées
            $user = new UserModel();
            $user->__constructWithData($row->id, $row->name, $row->firstname, $row->email, $row->password, $row->role, $row->image);

            return $user; // Retourner l'objet utilisateur
        }

        return null; // Si l'utilisateur n'existe pas, retourner null
    }

    // Constructeur alternatif qui permet de définir les données directement
    public function __constructWithData($id, $name, $firstname, $email, $password, $role, $image) {
        $this->id = $id;
        $this->name = $name;
        $this->firstname = $firstname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->image = $image;
    }

    // Méthodes pour accéder aux propriétés de l'utilisateur
    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }
    public function getFirstname(): string {
        return $this->firstname;
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
    public function getImage(): string {
        return $this->image;
    }
}
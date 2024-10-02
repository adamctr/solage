<?php

class AdminModel { //définiition des propriété de la classe
    protected $db;
    protected $id;
    protected $username;
    protected $password;
    protected $email;
    protected $roles;

    public function __construct() { //constructeur : initialise la connexion avec la bdd
        $this->db = DataBase::getConnection();
    }
    public function getAdmins(): array { //récupère les admins de la bdd
        $statement = $this->db->query('SELECT id, username, email FROM users WHERE role = 1 ORDER BY name ASC'); //selectionne les champs à récupérer

        $admins = [];//initialise le tableau ou vont se stocker les informations
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {        //par tous les résultats de la requête

            $admin = new AdminModel();//crée une nouvelle instance pour chaque admin
            $admin->__constructWithData($row->id, $row->name, $row->firstname, $row->email, $row->password, $row->role); //met les propriétés récupéreré à chacun des admin
            $admins[] = $admin; //ajouter info aux tableau
        }
        return $admins;
    }

    public function __constructXithData($id, $name, $firstname, $password, $email, $role)
    { //initialise les propriétés avec des valeurs spécifiques
        $this->id = $id;
        $this->name = $name;
        $this->firstname = $firstname;
        $this->password = $password;
        $this->email = $email;
        $this->role = $role;
    }
    public function getId(): int{
        return $this->id;
    }
    public function getname(): string{
        return $this->name;
    }
    public function getFirstname(): string{
        return $this->firstname;
    }
    public function getPassword(): string{
        return $this->password;
    }
    public function getEmail(): string{
        return $this->email;
    }
    public function getRole(): int{
        return $this->role;
    }

    public function deleteUser($userId) {
        $statement = $this->db->prepare('DELETE FROM users WHERE id = :userId');
        $statement->bindParam(':userId', $userId);
        $statement->execute();
    }

    public function updateUserRole($userId, $roleId) {
        $statement = $this->db->prepare('UPDATE users SET role = :roleId WHERE id = :userId');
        $statement->bindParam(':roleId', $roleId);
        $statement->bindParam(':userId', $userId);
        $statement->execute();
    }

    public function deletePost($postId) {
        $statement = $this->db->prepare('DELETE FROM posts WHERE id = :postId');
        $statement->bindParam(':postId', $postId);
        $statement->execute();
    }


}
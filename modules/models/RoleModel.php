<?php

class RoleModel
{
    protected $db;
    protected $id;
    protected $name;

    public function __construct()
    {
        $this->db = DataBase::getConnection();
    }

    public function getRoles(): array
    {
        $statement = $this->db->query('SELECT id, name FROM roles ORDER BY name ASC');
        $roles = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $role = new RoleModel();
            $role->__constructWithData($row->id, $row->name);
            $roles[] = $role;
        }
        return $roles;
    }

    public function __constructWithData($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }


    public function findRoleById(int $id): void
    {
        $statement = $this->db->prepare('
    SELECT roles.id, roles.name 
    FROM roles
    INNER JOIN users ON users.role = roles.id
    WHERE users.id = :userId');

        $statement->bindParam('userId', $id, PDO::PARAM_INT);  // Utilisation de $id au lieu de $userId
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_OBJ);
        if ($row) {
            $this->id = $row->id;
            $this->name = $row->name;
            //return new RoleModel($row->id, $row->name);
        } else {
            //return null;
        }
    }

}
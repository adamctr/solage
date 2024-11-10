<?php

class SearchModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function search($query) {
        $query = '%' . $query . '%';

        $sql = '
            SELECT p.id, p.user, p.date, p.likes, p.content, p.reply_to, p.image, p.reply_to_parent, u.name
            FROM posts p
            JOIN users u ON p.user = u.id
            WHERE u.name LIKE ? OR p.content LIKE ?
            ORDER BY p.date DESC
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([$query, $query]);

        $arrayResult = $statement->fetchAll(PDO::FETCH_ASSOC);
        $posts = []; // Array to hold PostModel instances

        foreach ($arrayResult as $row) {
            // Récupérer chaque donnée dans une variable
            $id = $row['id'];
            $user = $row['user'];
            $date = $row['date'];
            $likes = $row['likes'];
            $content = $row['content'];
            $replyTo = $row['reply_to'];
            $image = $row['image'];
            $replyToParent = $row['reply_to_parent'];
            $username = $row['name'];

            // Créer une instance de PostModel pour chaque post
            $post = new PostModel($id, $user, $content, $date, $likes, $replyTo, $image, $replyToParent);

            // Ajouter l'instance de PostModel au tableau
            $posts[] = $post;
        }

        return $posts; // Retourner les objets PostModel
    }

    public function searchPosts($query) {
        $query = '%' . $query . '%';

        $sql = '
            SELECT p.id, p.user, p.date, p.likes, p.content, p.reply_to, p.image, p.reply_to_parent, u.name
            FROM posts p
            JOIN users u ON p.user = u.id
            WHERE p.content LIKE ?
            ORDER BY p.date DESC
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([$query]);

        $arrayResult = $statement->fetchAll(PDO::FETCH_ASSOC);
        $posts = []; // Array to hold PostModel instances

        foreach ($arrayResult as $row) {
            // Récupérer chaque donnée dans une variable
            $id = $row['id'];
            $user = $row['user'];
            $date = $row['date'];
            $likes = $row['likes'];
            $content = $row['content'];
            $replyTo = $row['reply_to'];
            $image = $row['image'];
            $replyToParent = $row['reply_to_parent'];
            $username = $row['name'];

            // Créer une instance de PostModel pour chaque post
            $post = new PostModel($id, $user, $content, $date, $likes, $replyTo, $image, $replyToParent);

            // Ajouter l'instance de PostModel au tableau
            $posts[] = $post;
        }

        return $posts; // Retourner les objets PostModel
    }

    public function searchUsers($query) {
        $query = '%' . $query . '%';

        $sql = '
            SELECT id, name, email, password, role, image
            FROM users
            WHERE name LIKE ?
            ORDER BY name ASC
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([$query]);

        $arrayResult = $statement->fetchAll(PDO::FETCH_ASSOC);
        $users = []; // Array to hold PostModel instances

        foreach ($arrayResult as $row) {
            // Récupérer chaque donnée dans une variable
            $id = $row['id'];
            $name = $row['name'];
            $email = $row['email'];
            $password = $row['password'];
            $role = $row['role'];
            $image = $row['image'];

            // Créer un tableau avec les données de l'utilisateur
            $userData = (object) [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'image' => $image
            ];

            // Créer une instance de UserModel pour chaque utilisateur
            $user = new UserModel($userData);

            // Ajouter l'instance de UserModel au tableau
            $users[] = $user;
        }
        return $users; // Retourner les objets PostModel
    }
}

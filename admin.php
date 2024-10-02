<?php
// Inclure les fichiers nécessaires (Modèles, Contrôleurs, etc.)
require_once 'models/AdminModel.php';
require_once 'models/PostModel.php';
require_once 'models/RoleModel.php';

// Commencez la session pour vérifier si l'utilisateur est connecté et a les droits d'admin (si nécessaire)
// session_start();

// Vérifiez si l'utilisateur est bien un administrateur
// if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
//     header('Location: login.php'); // Rediriger vers la page de login si pas admin
//     exit;
// }

// Détecter les actions via le paramètre "action" dans l'URL
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Si la requête est POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Suppression d'un utilisateur
        if ($action === 'deleteUser') {
            $userId = $_POST['userId'];
            $adminModel = new AdminModel();
            $adminModel->deleteUser($userId);
            header('Location: admin.php'); // Recharge la page admin après suppression
            exit;
        }

        // Suppression d'un post
        if ($action === 'deletePost') {
            $postId = $_POST['postId'];
            $postModel = new PostModel();
            $postModel->deletePost($postId);
            header('Location: admin.php'); // Recharge la page admin après suppression
            exit;
        }

        // Modification du rôle d'un utilisateur
        if ($action === 'changeRole') {
            $userId = $_POST['userId'];
            $roleId = $_POST['roleId'];
            $adminModel = new AdminModel();
            $adminModel->updateUserRole($userId, $roleId);
            header('Location: admin.php'); // Recharge la page admin après modification
            exit;
        }
    }
}

// Récupérer les données à afficher
$adminModel = new AdminModel();
$roleModel = new RoleModel();
$postModel = new PostModel();

// Récupérer les utilisateurs, les rôles et les posts
$admins = $adminModel->getAdmins();
$roles = $roleModel->getRoles();
$posts = $postModel->getPosts(); // Suppose que cette méthode existe

// Charger la vue d'administration
require_once 'views/AdminView.php';
$view = new AdminView($admins, $roles, $posts);
$view->show();
?>

<?php
require '../vendor/autoload.php';



require_once '../includes/autoload.php';
Autoloader::register();

// Session démarrée au plus tôt, avant tout output possible.
// Garantit que le cookie PHPSESSID peut être posé même si le contrôleur
// echo'e du JSON avant d'appeler SessionController::login().
session_start();

define('APP_ENV', 'development');

if (APP_ENV === 'production') {
    $minificationController = new MinificationController();

    $minificationController->minifyAssets();
}

require_once '../includes/database.php';

require_once '../routes/index.php';

$userModel = new UserModel();






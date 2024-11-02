<?php
require '../vendor/autoload.php';



require_once '../includes/autoload.php';
Autoloader::register();
define('APP_ENV', 'production');

if (APP_ENV === 'production') {
    $minificationController = new MinificationController();

    $minificationController->minifyAssets();
}

require_once '../includes/database.php';

$migrations = new Migrations();
$migrations->migrate();

require_once '../routes/index.php';






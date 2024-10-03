<?php
require_once '../includes/autoload.php';
Autoloader::register();

require_once '../includes/database.php';
require_once '../routes/index.php';

$migrations = new Migrations();
$migrations->migrate();




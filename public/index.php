<?php
require_once '../includes/autoload.php';
Autoloader::register();

require_once '../includes/database.php';

$migrations = new Migrations();
$migrations->migrate();

require_once '../routes/index.php';






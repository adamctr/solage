<?php

//use models\RoleModel;

require_once '../includes/autoload.php';
Autoloader::register();

require_once '../includes/database.php';
require_once '../routes/index.php';

$adminModel = new AdminModel();
$roleModel = new RoleModel();
$roleModel->findRoleById(1);

var_dump($roleModel);



<?php
// Le troisième argument est le chemin jusqu'à la page, le 4ème le nom

$router->addRoute('GET', '/',  'HomepageController#execute');
$router->addRoute('GET', '/user/{id}',  'UserController#execute');

<?php

$router->addRoute('POST', '/api/users',  'HomepageController#test', AuthMiddleware::class);
$router->addRoute('POST', '/api/post',  'PostController#create', AuthMiddleware::class);
$router->addRoute('POST', '/api/like',  'LikeController#create', AuthMiddleware::class);
$router->addRoute('POST', '/login', 'UserController#login');
$router->addRoute('POST', '/register', 'UserController#register');
$router->addRoute('POST', '/api/users/delete', 'UserController#delete');
$router->addRoute('POST', '/api/posts/delete', 'PostController#delete');

$router->addRoute('POST', '/logout', 'UserController#logout');
$router->addRoute('POST', '/edituser/{id}', 'UserController#update');

// Validators :




<?php

$router->addRoute('POST', '/api/users',  'HomepageController#test', AuthMiddleware::class);
$router->addRoute('POST', '/api/post',  'PostController#create', AuthMiddleware::class);
$router->addRoute('POST', '/api/like',  'LikeController#create', AuthMiddleware::class);
$router->addRoute('POST', '/login', 'UserController#login');
$router->addRoute('POST', '/register', 'UserController#register');


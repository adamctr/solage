<?php


$router->addRoute('GET', '/',  'HomepageController#execute', AuthMiddleware::class);
$router->addRoute('GET', '/user/{id}',  'UserController#execute', AuthMiddleware::class);
$router->addRoute('GET', '/post/{id}',  'ResponseController#execute', AuthMiddleware::class);
$router->addRoute('GET', '/login',  'UserController#showLoginForm', '');
$router->addRoute('GET', '/register',  'UserController#showRegisterForm', '');

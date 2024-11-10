<?php


$router->addRoute('GET', '/',  'HomepageController#execute', AuthMiddleware::class);
$router->addRoute('GET', '/user/{id}',  'UserController#execute', AuthMiddleware::class);
$router->addRoute('GET', '/post/{id}',  'ResponseController#execute', AuthMiddleware::class);
$router->addRoute('GET', '/login',  'UserController#showLoginForm', '');
$router->addRoute('GET', '/register',  'UserController#showRegisterForm', '');

$router->addRoute('GET', '/search', 'SearchController#showSearchPage');
$router->addRoute('GET', '/search/results', 'SearchController#searchResults');
$router->addRoute('GET', '/edituser/{id}', 'UserController#update');
$router->addRoute('GET', '/admin', 'AdminController#showAdminPage, AdminMiddleware::class');
$router->addRoute('GET', '/admin/search/results/users', 'AdminController#showSearchUsersPage');
$router->addRoute('GET', '/admin/search/results/posts', 'AdminController#showSearchPostsPage');



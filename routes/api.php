<?php

$router->addRoute('POST', '/api/users',  'HomepageController#test');
$router->addRoute('POST', '/api/post',  'PostController#create');
$router->addRoute('POST', '/api/like',  'LikeController#create');

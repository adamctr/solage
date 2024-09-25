<?php

$router = new Router();

require __DIR__ . '/api.php';
require __DIR__ . '/front.php';

$router->match();

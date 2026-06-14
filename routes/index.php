<?php

declare(strict_types=1);

$router = new Router();

require_once __DIR__ . '/api.php';
require_once __DIR__ . '/front.php';

$router->match();

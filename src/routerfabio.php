<?php

class Router
{
    protected $routes = [];

    public function addRoute($method, $path, $target)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'target' => $target,
        ];

    }

    public function match()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($uri, PHP_URL_PATH);
        $debug = false;

        if ($debug) {
            echo "Requête URI: " . $requestUri . "<br>";
            echo "Méthode: " . $requestMethod . "<br>";
        }

        foreach ($this->routes as $route) {
            $pathRegex = preg_replace('/{([a-zA-Z0-9]+)}/', '([a-zA-Z0-9_]+)', $route['path']);
            $pathRegex = '#^' . $pathRegex . '$#';

            if ($debug) {
                echo "Comparaison avec: " . $pathRegex . "<br>";
            }

            if ($route['method'] === $requestMethod && preg_match($pathRegex, $requestUri, $matches)) {
                $routeArray = explode('#', $route['target']);
                $controller = $routeArray[0];
                $functionController = $routeArray[1];


                var_dump($matches);
                // Extraire les arguments de l'URL (ex. : l'ID utilisateur) et les passer à la méthode du contrôleur
                array_shift($matches); // On enlève le premier élément qui correspond à la route complète
                $userId = $matches[0];
                // Instancier le contrôleur
                $instance = new $controller($userId);
                call_user_func_array([$instance, $functionController], $matches);

                return;
            }

        }

        http_response_code(404);
        echo "Not Found";
    }
}
<?php

class Router {
    protected $routes = [];
    public function addRoute($method, $path, $target) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'target' => $target,
        ];

    }

    public function match() {
        $uri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            $pathRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $route['path']);
            $pathRegex = '#^' . $pathRegex . '$#';

            if ($route['method'] === $requestMethod && preg_match($pathRegex, $requestUri, $matches)) {

                $routeArray = explode('#', $route['target']);
                //var_dump($routeArray);
                $controller = $routeArray[0];
                $functionController = $routeArray[1];
                $instance = new $controller();
                $instance->$functionController();

                //array_shift($matches);
                //call_user_func_array($route['target'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo "Not Found";
    }
}



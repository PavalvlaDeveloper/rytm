<?php
namespace RyTM\Core;

class Router
{
    private $routes = [];

    public function add($method, $uri, $controllerAction)
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controllerAction[0],
            'action' => $controllerAction[1]
        ];
    }

    public function dispatch($method, $uri)
    {
        $uri = strtok($uri, '?');
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['uri'] === $uri) {
                $controller = new $route['controller']();
                return $controller->{$route['action']}();
            }
        }
        http_response_code(404);
        View::render('error/404');
    }
}
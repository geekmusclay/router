<?php

declare(strict_types=1);

namespace Geekmusclay\Router;

use Exception;
use Geekmusclay\Router\Route;

use function trim;

class Router
{
    /** @var string $url Url to be matched */
    private string $url;

    /** @var Route[] $routes Collection of router routes */
    private array $routes = [];

    /** @var Route[] $namesRoutes Collection of named routes */
    private array $namedRoutes = [];

    /**
     * Router constructor
     *
     * @param string $url The url that we want to match
     */
    public function __construct(string $url)
    {
        $this->url = trim($url, '/');
    }

    /**
     * Router GET function, create a route
     *
     * @param string         $path     Path of the route
     * @param array|callable $callable Callable to execute on route match
     * @param string|null    $name     Name of the route
     */
    public function get(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'GET', $name);
    }

    /**
     * Router POST function, create a route
     *
     * @param string         $path     Path of the route
     * @param array|callable $callable Callable to execute on route match
     * @param string|null    $name     Name of the route
     */
    public function post(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'POST', $name);
    }

    /**
     * Router PUT function, create a route
     *
     * @param string         $path     Path of the route
     * @param array|callable $callable Callable to execute on route match
     * @param string|null    $name     Name of the route
     */
    public function put(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'PUT', $name);
    }

    /**
     * Router PATCH function, create a route
     *
     * @param string         $path     Path of the route
     * @param array|callable $callable Callable to execute on route match
     * @param string|null    $name     Name of the route
     */
    public function patch(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'PATCH', $name);
    }

    /**
     * Router DELETE function, create a route
     *
     * @param string         $path     Path of the route
     * @param array|callable $callable Callable to execute on route match
     * @param string|null    $name     Name of the route
     */
    public function delete(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'DELETE', $name);
    }

    /**
     * Router POST function, create a route
     *
     * @param string         $path     Path of the route
     * @param array|callable $callable Callable to execute on route match
     * @param string|null    $name     Name of the route
     */
    private function add(
        string $path,
        $callable,
        string $method,
        ?string $name = null
    ): Route {
        $route                   = new Route($path, $callable);
        $this->routes[$method][] = $route;
        if (null !== $name) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    /**
     * Get route url by his name
     *
     * @param string $name   The name of the route
     * @param array  $params The params that are passed in the url
     */
    public function path(string $name, array $params = []): ?string
    {
        if (false === isset($this->namedRoutes[$name])) {
            throw new Exception('Named route not found');
        }

        return $this->namedRoutes[$name]->path($params);
    }

    /**
     * Undocumented function
     *
     * @return mixed
     * @throws Exception
     */
    public function run()
    {
        if (false === isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
            throw new Exception('REQUEST_METHOD does not exist');
        }

        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if (true === $route->match($this->url)) {
                return $route->call();
            }
        }

        throw new Exception('No matching routes');
    }
}

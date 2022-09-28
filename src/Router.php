<?php

declare(strict_types=1);

namespace Geekmusclay\Router;

use Exception;
use Geekmusclay\Router\Route;
use Psr\Http\Message\ServerRequestInterface;

use function trim;

class Router
{
    /** @var Route[] $routes Collection of router routes */
    private array $routes = [];

    /** @var Route[] $namesRoutes Collection of named routes */
    private array $namedRoutes = [];

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
            $route->setName($name);
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
     * Find route by his name
     *
     * @param string $name Name of the searched route
     *
     * @return Route|null
     */
    public function find(string $name): ?Route
    {
        if (false === isset($this->namedRoutes[$name])) {
            return null;
        }

        return $this->namedRoutes[$name];
    }

    /**
     * Look for the corresponding route of given request
     *
     * @param ServerRequestInterface $request Request to match
     *
     * @return Route Matched route
     */
    public function match(ServerRequestInterface $request): Route
    {
        /** @var string $uri Current request uri */
        $uri = $request->getUri()->getPath();
        $uri = trim($uri, '/');

        /** @var string $method Request method */
        $method = $request->getMethod();
        if (false === isset($this->routes[$method])) {
            throw new Exception('REQUEST_METHOD does not exist');
        }

        foreach ($this->routes[$method] as $route) {
            if (true === $route->match($uri)) {
                return $route;
            }
        }

        throw new Exception('No matching routes');
    }

    /**
     * Function to launch the router, it will look for the
     * corresponding route and then launch the callback.
     *
     * @return mixed
     * @throws Exception
     */
    public function run(ServerRequestInterface $request)
    {
        return $this->match($request)->call();
    }
}

<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Core;

use Exception;
use Geekmusclay\Router\Attribute\Route as AttributeRoute;
use Geekmusclay\Router\Core\Route;
use Geekmusclay\Router\Interfaces\RouteInterface;
use Geekmusclay\Router\Interfaces\RouterInterface;
use Geekmusclay\Router\Proxies\RouterProxy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

use function trim;

class Router implements RouterInterface
{
    /** @var array<string, Route[]> $routes Collection of router routes */
    private array $routes = [];

    /** @var array<string, Route> $namedRoutes Collection of named routes */
    private array $namedRoutes = [];

    /** @var ContainerInterface $container Dependency injection container */
    private ?ContainerInterface $container = null;

    /**
     * Router constructor function.
     *
     * @param ContainerInterface|null $container Dependency injection container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Router GET function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function get(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'GET', $name);
    }

    /**
     * Router POST function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function post(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'POST', $name);
    }

    /**
     * Router PUT function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function put(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'PUT', $name);
    }

    /**
     * Router PATCH function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function patch(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'PATCH', $name);
    }

    /**
     * Router DELETE function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function delete(string $path, $callable, ?string $name = null): Route
    {
        return $this->add($path, $callable, 'DELETE', $name);
    }

    /**
     * Router POST function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function add(
        string $path,
        $callable,
        string $method,
        ?string $name = null
    ): Route {
        $route                   = new Route($path, $callable, $name);
        $this->routes[$method][] = $route;
        if (null !== $name) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    /**
     * Will allow routes to be declared in a group, using a suffix
     *
     * @param  string   $suffix   Group suffix
     * @param  callable $callable Callable to execute (contains routes declaration)
     * @return mixed
     */
    public function group(string $suffix, callable $callable)
    {
        return $callable(new RouterProxy($suffix, $this));
    }

    /**
     * Get route url by his name
     *
     * @param string  $name   The name of the route
     * @param mixed[] $params The params that are passed in the url
     * @throws Exception Throw exception when route does not exist
     */
    public function path(string $name, array $params = []): string
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
     */
    public function find(string $name): ?Route
    {
        if (false === isset($this->namedRoutes[$name])) {
            return null;
        }

        return $this->namedRoutes[$name];
    }

    /**
     * Registers a class / controller containing routes declared
     * using the "Route" attribute
     *
     * @param string $class The class to register
     */
    public function register(string $class): bool
    {
        $reflection = new ReflectionClass($class);

        $prefix = null;
        $attributes = $reflection->getAttributes(AttributeRoute::class);
        if (true === isset($attributes[0])) {
            $attribute = $attributes[0]->newInstance();
            $prefix = $attribute->getPath();
        }

        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(AttributeRoute::class);
            if (0 === count($attributes)) {
                continue;
            }
            $attribute = $attributes[0]->newInstance();
            $path = $prefix . $attribute->getPath();

            $route = $this->add(
                $path,
                [$class, $method->getName()],
                $attribute->getMethod(),
                $attribute->getName()
            );

            $with = $attribute->getWith();
            if (count($with) > 0) {
                $route->with($with);
            }
        }

        return true;
    }

    /**
     * Look for the corresponding route of given request
     *
     * @param ServerRequestInterface $request Request to match
     * @return Route|null Matched route, or null if nor route match
     */
    public function match(ServerRequestInterface $request): ?RouteInterface
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

        return null;
    }

    /**
     * Allow to flush router routes and named routes
     */
    public function flush(): void
    {
        $this->routes      = [];
        $this->namedRoutes = [];
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
        $route = $this->match($request);
        if (null === $route) {
            throw new Exception('No route found');
        }

        return $route->call($request, $this->container);
    }

    /**
     * Router getContainer function.
     *
     * @return ContainerInterface|null Dependency injection container
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Router setContainer function.
     *
     * @param ContainerInterface|null Dependency injection container
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Proxies;

use Geekmusclay\Router\Core\Router;
use Psr\Http\Message\ServerRequestInterface;
use Geekmusclay\Router\Interfaces\RouteInterface;
use Geekmusclay\Router\Interfaces\RouterInterface;

class RouterProxy implements RouterInterface
{
    private string $suffix;

    private Router $router;

    public function __construct(string $suffix, Router $router)
    {
        $this->suffix = $suffix;
        $this->router = $router;
    }

    /**
     * Router proxy GET function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function get(string $path, $callable, ?string $name = null): RouteInterface
    {
        $path = $this->suffix . $path;

        return $this->router->get($path, $callable, $name);
    }

    /**
     * Router POST function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function post(string $path, $callable, ?string $name = null): RouteInterface
    {
        $path = $this->suffix . $path;

        return $this->router->post($path, $callable, $name);
    }

    /**
     * Router PUT function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function put(string $path, $callable, ?string $name = null): RouteInterface
    {
        $path = $this->suffix . $path;

        return $this->router->put($path, $callable, $name);
    }

    /**
     * Router PATCH function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function patch(string $path, $callable, ?string $name = null): RouteInterface
    {
        $path = $this->suffix . $path;

        return $this->router->patch($path, $callable, $name);
    }

    /**
     * Router DELETE function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function delete(string $path, $callable, ?string $name = null): RouteInterface
    {
        $path = $this->suffix . $path;

        return $this->router->delete($path, $callable, $name);
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
        $suffix = $this->suffix . $suffix;

        return $callable(new RouterProxy($suffix, $this->router));
    }

    /**
     * Look for the corresponding route of given request
     *
     * @param ServerRequestInterface $request Request to match
     */
    public function match(ServerRequestInterface $request): ?RouteInterface
    {
        return $this->router->match($request);
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
        return $this->router->path($name, $params);
    }

    /**
     * Registers a class / controller containing routes declared
     * using the "Route" attribute
     *
     * @param string $class The class to register
     */
    public function register(string $class): void
    {
        $this->router->register($class);
    }

    /**
     * Starts the "register" function on all files that will be found in the given folder,
     * as well as in all subfolders.
     *
     * @param  string $path      The path to the root directory
     * @param  string $namespace The root namespace
     * @return array             Return an array of result for the needs of the recursive feature
     */
    public function registerDir(string $path, string $namespace): array
    {
        return $this->router->registerDir($path, $namespace);
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
        return $this->router->run($request);
    }
}

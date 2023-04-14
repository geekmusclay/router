<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Interfaces;

use Psr\Http\Message\ServerRequestInterface;
use Geekmusclay\Router\Interfaces\RouteInterface;

interface RouterInterface
{
    /**
     * Router GET function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function get(string $path, $callable, ?string $name = null): RouteInterface;

    /**
     * Router POST function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function post(string $path, $callable, ?string $name = null): RouteInterface;

    /**
     * Router PUT function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function put(string $path, $callable, ?string $name = null): RouteInterface;

    /**
     * Router PATCH function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function patch(string $path, $callable, ?string $name = null): RouteInterface;

    /**
     * Router DELETE function, create a route
     *
     * @param string            $path     Path of the route
     * @param string[]|callable $callable Callable to execute on route match
     * @param string|null       $name     Name of the route
     */
    public function delete(string $path, $callable, ?string $name = null): RouteInterface;

    /**
     * Will allow routes to be declared in a group, using a suffix
     *
     * @param  string   $suffix   Group suffix
     * @param  callable $callable Callable to execute (contains routes declaration)
     * @return mixed
     */
    public function group(string $suffix, callable $callable);

    /**
     * Look for the corresponding route of given request
     *
     * @param ServerRequestInterface $request Request to match
     */
    public function match(ServerRequestInterface $request): ?RouteInterface;

    /**
     * Get route url by his name
     *
     * @param string  $name   The name of the route
     * @param mixed[] $params The params that are passed in the url
     * @throws Exception Throw exception when route does not exist
     */
    public function path(string $name, array $params = []): string;

    /**
     * Registers a class / controller containing routes declared
     * using the "Route" attribute
     *
     * @param string $class The class to register
     */
    public function register(string $class): void;

    /**
     * Function to launch the router, it will look for the
     * corresponding route and then launch the callback.
     *
     * @return mixed
     * @throws Exception
     */
    public function run(ServerRequestInterface $request);
}

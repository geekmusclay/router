<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Core;

use Exception;
use Geekmusclay\Router\Interfaces\RouteInterface;
use Geekmusclay\Router\Interfaces\ServerMiddlewareInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Safe\Exceptions\PcreException;

use function array_shift;
use function call_user_func_array;
use function count;
use function is_array;
use function is_numeric;
use function preg_replace_callback;
use function Safe\preg_match;
use function str_replace;
use function strpos;
use function trim;

class Route implements RouteInterface
{
    /** @var string $path Route path */
    private string $path;

    /** @var string[]|callable $callable Route callback function */
    private $callable;

    /** @var string[] $matches Matches from preg match */
    private array $matches = [];

    /** @var string[] $params Route parameters */
    private array $params = [];

    /** @var string|null $name Route name */
    private ?string $name;

    /** @var ServerMiddlewareInterface[] $middlewares Contain the route middlewares */
    private array $middlewares = [];

    private int $index = 0;

    /**
     * @param string            $path     Route path
     * @param string[]|callable $callable Route callback function
     *                                    or array to specify controller and function
     */
    public function __construct(string $path, $callable, ?string $name = null)
    {
        $this->path     = $path;
        $this->callable = $callable;
        $this->name     = $name;
    }

    /**
     * Returns the url of the route with the given parameters
     *
     * @param mixed[] $params Params to build url
     */
    public function path(array $params): string
    {
        $url = $this->path;
        foreach ($params as $param => $value) {
            $url = str_replace(':' . (string) $param, (string) $value, $url);
        }

        return $url;
    }

    /**
     * Checks if the route matches the url
     *
     * @param string $url Url to check
     * @throws PcreException
     */
    public function match(string $url): bool
    {
        $path = trim($this->path, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $path);
        if (null === $path) {
            return false;
        }
        $regex = "/^" . str_replace('/', '\/', $path) . "$/i";

        if (0 === preg_match($regex, $url, $this->matches)) {
            return false;
        }
        array_shift($this->matches);

        return true;
    }

    /**
     * Checks if a regex has already been defined for the given parameter
     *
     * @param string[] $match List of regex
     */
    private function paramMatch(array $match): string
    {
        if (true === isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }

        return '([^/]+)';
    }

    /**
     * Defines a regex for a given url parameter
     *
     * @param string[] $params Associative array of parameter and regex
     */
    public function with(array $params): self
    {
        foreach ($params as $param => $regex) {
            $this->params[$param] = str_replace('(', '(?:', $regex);
        }

        return $this;
    }

    /**
     * @todo replace this with DI Container
     * Function used to inject parameters when calling a function
     * @param ServerRequestInterface $request The current request
     * @return array<mixed>
     */
    private function getToPass(ServerRequestInterface $request, ResponseInterface $response): array
    {
        if (is_object($this->callable)) {
            $reflector = new ReflectionFunction($this->callable);
        } else if (false === isset($this->callable[0]) || false === isset($this->callable[1])) {
            return [];
        } else {
            $reflector = new ReflectionMethod($this->callable[0], $this->callable[1]);
        }
        $matches   = $this->getMatches();
        $params    = $reflector->getParameters();

        $toPass = [];
        foreach ($params as $param) {
            $type = $param->getType();
            if (null === $type) {
                continue;
            }
            $type = $type->getName();
            $name = $param->getName();

            if (
                ServerRequestInterface::class === $type ||
                RequestInterface::class === $type
            ) {
                $toPass[] = $request;
            } else if (ResponseInterface::class === $type) {
                $toPass[] = $response;
            } else {
                $toPass[] = $matches[$name];
            }
        }

        return $toPass;
    }

    /**
     * Executes the route callable
     *
     * @return mixed The return is almost whatever is inside callable
     */
    public function call(ServerRequestInterface $request, ?ContainerInterface $container = null)
    {
        if (count($this->middlewares) > 0) {
            $res = $this->processMiddlewares($request);
            if (false === $res) {
                throw new Exception('Middleware failed');
            }
        }

        $response = new Response();
        $toPass = $this->getToPass($request, $response);
        if (true === is_array($this->callable) && 2 === count($this->callable)) {

            if (null !== $container) {
                $callable = $container->get($this->callable[0]);
            } else {
                $callable = new $this->callable[0]();
            }

            return call_user_func_array(
                [
                    $callable,
                    $this->callable[1],
                ],
                $this->cast($toPass)
            );
        }

        return call_user_func_array($this->callable, $this->cast($toPass));
    }

    /**
     * @todo find another way to cast params
     *
     * Cast numeric parameters
     * @param string[] $params Parameters to cast
     * @return mixed[] Casted array values
     */
    private function cast(array $params): array
    {
        foreach ($params as &$param) {
            if (true === is_numeric($param)) {
                $float = (float) $param;
                $int   = (int) $param;
                if (($float - $int) > 0) {
                    $param = $float;
                } else {
                    $param = $int;
                }
            }
        }

        return $params;
    }

    /**
     * Return the route parameters
     *
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * Get route matches after running the match function
     *
     * @return string[] Matches foud in the url
     * @throws PcreException
     */
    public function getMatches(): array
    {
        $res = [];
        foreach ($this->params as $param => $regex) {
            $regex = '(' . $regex . ')';
            foreach ($this->matches as $match) {
                if (1 === preg_match($regex, $match)) {
                    $res[ $param ] = $match;
                }
            }
        }

        return $res;
    }

    /**
     * Get route callable or the array that contain the controller
     * name and the function to execute in it.
     */
    public function getCallback(): callable
    {
        if (
            true === is_array($this->callable) &&
            false === strpos($this->callable[0], '::') &&
            2 === count($this->callable)
        ) {
            return [
                new $this->callable[0](),
                $this->callable[1],
            ];
        }

        return $this->callable;
    }

    /**
     * Return route name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Add middlewares to route
     *
     * @param array $middlewares The middlewares to add
     */
    public function withMiddleware(array $middlewares)
    {
        $res = [];
        foreach ($middlewares as $middleware) {
            $res[] = new $middleware();
        }

        $this->middlewares = $res;
    }

    /**
     * Used to preccess route Middlewares.
     *
     * @param ServerRequestInterface $request The current request
     * @return mixed Depend on middlewares way to work
     */
    public function processMiddlewares(ServerRequestInterface $request)
    {
        $middleware = $this->getMiddleware();
        if (null === $middleware) {
            throw new Exception('Bad middleware chain');
        }

        return call_user_func_array($middleware, [$request, [$this, 'processMiddlewares']]);
    }

    /**
     * Gte middleware according to class index property.
     *
     * @return callable|null Return null if there is no more middleware
     */
    private function getMiddleware(): ?callable
    {
        if (true === isset($this->middlewares[$this->index])) {
            $middleware = $this->middlewares[$this->index];
            $this->index++;

            return $middleware;
        }

        return null;
    }
}

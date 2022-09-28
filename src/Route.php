<?php

declare(strict_types=1);

namespace Geekmusclay\Router;

use function array_shift;
use function call_user_func_array;
use function count;
use function floatval;
use function intval;
use function is_array;
use function is_numeric;
use function preg_match;
use function preg_replace_callback;
use function str_replace;
use function trim;

class Route
{
    /** @var string $path Route path */
    private string $path;

    /** @var array|callable $callable Route callback function */
    private $callable;

    /** @var string[] $matches Matches from preg match */
    private array $matches = [];

    /** @var array $params Route parameters */
    private array $params = [];

    /** @var string|null $name Route name */
    private ?string $name;

    /**
     * @param string         $path     Route path
     * @param array|callable $callable Route callback function
     *                                 or array to specify controller and function
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
     * @param array $params Params to build url
     */
    public function path(array $params): string
    {
        $url = $this->path;
        foreach ($params as $param => $value) {
            $url = str_replace(':' . $param, $value, $url);
        }

        return $url;
    }

    /**
     * Checks if the route matches the url
     *
     * @param string $url Url to check
     */
    public function match(string $url): bool
    {
        $path  = trim($this->path, '/');
        $path  = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $path);
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
     * @param array $match List of regex
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
     * @param array $params Associative array of parameter and regex
     */
    public function with(array $params): self
    {
        foreach ($params as $param => $regex) {
            $this->params[$param] = str_replace('(', '(?:', $regex);
        }

        return $this;
    }

    /**
     * Executes the route callable
     *
     * @return mixed The return is almost whatever is inside callable
     */
    public function call()
    {
        if (true === is_array($this->callable) && 2 === count($this->callable)) {
            return call_user_func_array(
                [
                    new $this->callable[0](),
                    $this->callable[1],
                ],
                $this->cast($this->matches)
            );
        }

        return call_user_func_array($this->callable, $this->cast($this->matches));
    }

    /**
     * @todo find another way to cast params
     *
     * Cast numeric parameters
     * @param array $params Parameters to cast
     */
    private function cast(array $params): array
    {
        for ($i = 0; $i < count($params); $i++) {
            if (true === is_numeric($params[$i])) {
                if (((float) $params[$i] - (int) $params[$i]) > 0) {
                    $params[$i] = floatval($params[$i]);
                } else {
                    $params[$i] = intval($params[$i]);
                }
            }
        }

        return $params;
    }

    /**
     * Return the route parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * Get route matches after running the match function
     *
     * @return array Matches foud in the url
     */
    public function getMatches(): array
    {
        $res = [];
        foreach ($this->params as $param => $regex) {
            $regex = '(' . $regex . ')';
            foreach ($this->matches as $match) {
                if (preg_match($regex, $match)) {
                    $res[$param] = $match;
                }
            }
        }

        return $res;
    }

    /**
     * Get route callable or the array that contain the controller
     * name and the function to execute in it.
     *
     * @return callable|array
     */
    public function getCallback()
    {
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
     * Set route name
     *
     * @param string $name The name of the route
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}

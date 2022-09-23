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

    /**
     * @param string         $path     Route path
     * @param array|callable $callable Route callback function
     *                                  or string to specify controller and function
     */
    public function __construct(string $path, $callable)
    {
        $this->path     = $path;
        $this->callable = $callable;
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
     * Executes the route callable
     *
     * @return mixed
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
}

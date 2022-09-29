<?php

declare(strict_types=1);

namespace Geekmusclay\Router;

use Safe\Exceptions\PcreException;

use function array_shift;
use function call_user_func_array;
use function count;
use function floatval;
use function intval;
use function is_array;
use function is_numeric;
use function preg_replace_callback;
use function Safe\preg_match;
use function str_replace;
use function trim;

class Route
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
            $url = str_replace(':' . $param, $value, $url);
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
     * @param string[] $params Parameters to cast
     * @return mixed[] Casted array values
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
     * @return callable|string[]
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

<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RouteInterface
{
    /**
     * Checks if the route matches the url
     *
     * @param string $url Url to check
     * @throws PcreException
     */
    public function match(string $url): bool;

    /**
     * Executes the route callable
     *
     * @return mixed The return is almost whatever is inside callable
     */
    public function call(ServerRequestInterface $request);

    /**
     * Defines a regex for a given url parameter
     *
     * @param string[] $params Associative array of parameter and regex
     */
    public function with(array $params): self;
}

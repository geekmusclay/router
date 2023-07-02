<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    /**
     * Process an incoming server request in order to produce a response.
     *
     * @param ServerRequestInterface $request The server request
     * @param callable|null          $next    Callable to call after process
     * @return ResponseInterface|bool The produced response or boolean
     */
    public function __invoke(
        ServerRequestInterface $request,
        ?callable $next = null
    ): ResponseInterface|bool;
}

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
     * @param ServerRequestInterface $request  The server request
     * @param ResponseInterface|null $response The server response
     * @param callable|null          $next     Callable to call after process
     *
     * @return ResponseInterface|callable|bool The produced response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ?ResponseInterface $response = null,
        ?callable $next = null
    ): ResponseInterface|bool;
}

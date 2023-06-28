<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ServerMiddlewareInterface
{
    /**
     * Middleware __invoke function
     *
     * @param ServerRequestInterface $request  The server request
     * @param ResponseInterface      $response The server response
     * @param callable               $next     Callable to call after process
     *
     * @return bool The success of the operation
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): bool;
}
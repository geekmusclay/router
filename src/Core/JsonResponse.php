<?php

declare(strict_types=1);

namespace Geekmusclay\Router\Core;

use GuzzleHttp\Psr7\Response;

class JsonResponse extends Response
{
    /**
     * JsonResponse Constructor
     *
     * @param array       $headers
     * @param array|null  $body
     */
    public function __construct(
        int $status = 200,
        array $headers = [],
        $body = [],
        string $version = '1.1',
        ?string $reason = null
    ) {
        parent::__construct(
            $status,
            $headers,
            $body,
            $version,
            $reason
        );
    }
}

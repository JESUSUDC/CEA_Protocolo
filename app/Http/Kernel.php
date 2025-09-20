<?php
declare(strict_types=1);

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     */
    protected $middleware = [
        // middleware globales de Laravel (opcional)
    ];

    /**
     * Route middleware.
     */
    protected $routeMiddleware = [
    'jwt.auth' => \App\Infrastructure\Entrypoint\Rest\Middleware\JwtAuthMiddleware::class,
    // ...
];

}

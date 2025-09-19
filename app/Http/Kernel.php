<?php
declare(strict_types=1);

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Infrastructure\Entrypoint\Rest\Middleware\JwtAuthMiddleware;

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
        'jwt.auth' => JwtAuthMiddleware::class,
        // otros middlewares
    ];
}

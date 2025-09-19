protected $routeMiddleware = [
    // ...
    'jwt.auth' => \Infrastructure\Entrypoint\Rest\Middleware\JwtAuthMiddleware::class,
];

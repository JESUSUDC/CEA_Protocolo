<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Application\Users\Port\Out\TokenIssuerPort;
use App\Application\Users\Port\Out\UserRepositoryPort;

final class JwtAuthMiddleware
{
    public function __construct(
        private TokenIssuerPort $tokenIssuer,
        private UserRepositoryPort $userRepository,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        // 1) Leer header
        $authHeader = $request->headers->get('Authorization', '');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $this->safeLog('warning', 'Missing or malformed Authorization header');
            return $this->unauthorizedResponse('missing_token', 'Bearer token not provided');
        }

        $token = trim(substr($authHeader, 7));
        if ($token === '') {
            $this->safeLog('warning', 'Empty bearer token');
            return $this->unauthorizedResponse('missing_token', 'Bearer token not provided');
        }

        // 2) Validar token
        try {
            $this->safeLog('warning', 'Invalid access token: ' . $token);
            if (!$this->tokenIssuer->validateAccessToken($token)) {
                $this->safeLog('warning', 'Invalid access token');
                return $this->unauthorizedResponse('invalid_token', 'Invalid access token');
            }

            $claims = $this->tokenIssuer->getClaimsFromToken($token);
            
            if (!is_array($claims) || empty($claims['sub'])) {
                $this->safeLog('warning', 'Token missing subject or invalid claims');
                return $this->unauthorizedResponse('invalid_token', 'Invalid token claims');
            }

            // 3) Recuperar usuario
            $user = $this->userRepository->findById($claims['sub']);
            
            if ($user === null) {
                $this->safeLog('warning', 'User from token not found', ['sub' => $claims['sub']]);
                return $this->unauthorizedResponse('user_not_found', 'User not found');
            }

            // Verificar si el usuario tiene método isActive (usando reflexión para no acoplar)
            $reflection = new \ReflectionClass($user);
            if ($reflection->hasMethod('isActive') && !$user->isActive()) {
                $this->safeLog('warning', 'User is inactive', ['sub' => $claims['sub']]);
                return $this->unauthorizedResponse('user_inactive', 'User is inactive');
            }

            // 4) Inyectar user en la request
            $request->attributes->set('auth_user', $user);
            $request->attributes->set('auth_token', $token);
            $request->attributes->set('auth_claims', $claims);

            return $next($request);
            
        } catch (\Throwable $e) {
            $this->safeLog('error', 'Token validation error: ' . $e->getMessage());
            $message = config('app.debug') ? $e->getMessage() : 'Invalid access token';
            return $this->unauthorizedResponse('token_validation_error', $message);
        }
    }

    private function unauthorizedResponse(string $error, string $message = null): JsonResponse
    {
        $payload = [
            'error' => $error,
            'message' => $message ?? 'Unauthorized',
        ];
        return response()->json($payload, 401);
    }

    private function safeLog(string $level, string $message, array $context = []): void
    {
        // Filtrar información sensible del contexto
        $safeContext = array_filter($context, function ($key) {
            return !in_array($key, ['password', 'token', 'access_token', 'refresh_token']);
        }, ARRAY_FILTER_USE_KEY);

        match($level) {
            'error' => Log::error($message, $safeContext),
            'warning' => Log::warning($message, $safeContext),
            default => Log::info($message, $safeContext)
        };
    }
}
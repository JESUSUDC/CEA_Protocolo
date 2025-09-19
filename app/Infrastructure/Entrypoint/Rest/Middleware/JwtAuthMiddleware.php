<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Application\Security\TokenIssuerPort;

final class JwtAuthMiddleware
{
    public function __construct(private TokenIssuerPort $tokenIssuer)
    {
    }

    /**
     * Expect Authorization: Bearer {token}
     */
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization', $request->header('authorization', ''));

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $token = trim(substr($authHeader, 7));
        if ($token === '') {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Validate signature/expiration via TokenIssuerPort
        $valid = false;
        try {
            $valid = $this->tokenIssuer->validate($token);
        } catch (\Throwable $e) {
            // don't reveal internal errors: return 401
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$valid) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Token is valid; extract claims from payload (we already validated signature via port)
        // NOTE: this extracts payload without verifying signature — signature already checked by validate()
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        [$headerB64, $payloadB64, $sig] = $parts;

        $payloadJson = $this->base64UrlDecode($payloadB64);
        $claims = json_decode($payloadJson, true);

        if (!is_array($claims)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Attach claims to request for later use (controller / policies / usecases can read)
        // Example: $request->attributes->get('jwt_claims')
        $request->attributes->set('jwt_claims', $claims);

        // Optionally set a lightweight "auth user" (array) — not a Domain User object to avoid IO in middleware
        // $request->attributes->set('auth_user', ['id' => $claims['sub'] ?? null, 'username' => $claims['username'] ?? null, 'role' => $claims['role'] ?? null]);

        return $next($request);
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

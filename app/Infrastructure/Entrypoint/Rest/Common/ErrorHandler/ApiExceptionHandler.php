<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Common\ErrorHandler;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiExceptionHandler
{
    public static function handle(\Throwable $e): JsonResponse
    {
        // Map domain exceptions to HTTP codes
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $payload = ['error' => 'unexpected_error', 'message' => $e->getMessage()];

        $class = get_class($e);
        // Domain exceptions namespace patterns (adjust)
        if (str_contains($class, 'Domain\\Users\\Exception') || str_contains($class, 'Domain\\Cellphone\\Exception')) {
            $status = Response::HTTP_BAD_REQUEST;
            $payload['error'] = 'domain_error';
        }

        // add more mapping (NotFound, Unauthorized, Validation etc.)

        return response()->json($payload, $status);
    }
}

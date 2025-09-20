<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Common\ErrorHandler;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

final class ApiExceptionHandler
{
    public static function handle(\Throwable $e): JsonResponse
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $payload = ['error' => 'unexpected_error', 'message' => 'Internal server error'];

        // Si estamos en entorno local o APP_DEBUG=true, incluimos mensaje real (útil para debugging)
        $debug = config('app.debug') === true || env('APP_DEBUG') === 'true';

        $class = get_class($e);

        // 1) Not found exceptions (convención: cualquier clase que termine en "NotFoundException")
        if (str_ends_with($class, 'NotFoundException')) {
            $status = Response::HTTP_NOT_FOUND;
            $payload['error'] = 'not_found';
            $payload['message'] = $debug ? $e->getMessage() : 'Resource not found';
            return response()->json($payload, $status);
        }

        // 2) Validation exceptions -> 422
        if ($e instanceof ValidationException) {
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
            $payload['error'] = 'validation_error';
            $payload['message'] = $debug ? $e->getMessage() : 'Validation failed';
            // attach validation errors if available
            if (method_exists($e, 'errors')) {
                $payload['errors'] = $e->errors();
            }
            return response()->json($payload, $status);
        }

        // 3) Authentication -> 401
        if ($e instanceof AuthenticationException) {
            $status = Response::HTTP_UNAUTHORIZED;
            $payload['error'] = 'unauthenticated';
            $payload['message'] = $debug ? $e->getMessage() : 'Unauthenticated';
            return response()->json($payload, $status);
        }

        // 4) Domain exceptions (generic) -> 400
        if (str_contains($class, 'Domain\\') && str_contains($class, '\\Exception')) {
            $status = Response::HTTP_BAD_REQUEST;
            $payload['error'] = 'domain_error';
            $payload['message'] = $debug ? $e->getMessage() : 'Business rule violation';
            return response()->json($payload, $status);
        }

        // Other exceptions: preserve message in debug, generic otherwise
        if ($debug) {
            $payload['message'] = $e->getMessage();
            // optionally include trace in debug
            $payload['trace'] = $e->getTrace();
        }

        return response()->json($payload, $status);
    }
}

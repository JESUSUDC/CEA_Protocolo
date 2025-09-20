<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Common\ErrorHandler;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use App\Domain\Users\Exception\DomainException as UsersDomainException; // IMPORTANTE

final class ApiExceptionHandler
{
    public static function handle(\Throwable $e): JsonResponse
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $payload = ['error' => 'unexpected_error', 'message' => 'Internal server error'];

        $debug = config('app.debug') === true || env('APP_DEBUG') === 'true';

        // 1) Not found exceptions (convención: terminar en "NotFoundException" o instanceof UserNotFound)
        if (str_ends_with(get_class($e), 'NotFoundException')) {
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

        // 4) Domain exceptions -> 4xx (mapear a 422 o 400 según convención)
        // Usamos instanceof para detectar correctamente las excepciones del dominio
        if ($e instanceof UsersDomainException || is_subclass_of(get_class($e), UsersDomainException::class)) {
            $status = Response::HTTP_UNPROCESSABLE_ENTITY; // o BAD_REQUEST si preferís
            $payload['error'] = 'domain_error';
            $payload['message'] = $debug ? $e->getMessage() : 'Business rule violation';

            // Si es NotFound específico convertido a DomainException, podrías mapearlo a 404 arriba
            return response()->json($payload, $status);
        }

        // Otros: conservar mensaje en debug
        if ($debug) {
            $payload['message'] = $e->getMessage();
            $payload['trace'] = $e->getTrace();
        }

        return response()->json($payload, $status);
    }
}

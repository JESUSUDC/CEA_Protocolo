<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Common\ErrorHandler;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Users\Exception\DomainException as UsersDomainException;
use App\Domain\Users\Exception\InvalidPassword;
use App\Domain\Cellphone\Exception\DomainException as CellphoneDomainException;

final class ApiExceptionHandler
{
    private const ERROR_MAP = [
        ValidationException::class => [
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'error' => 'validation_error',
            'message' => 'Validation failed'
        ],
        AuthenticationException::class => [
            'status' => Response::HTTP_UNAUTHORIZED,
            'error' => 'unauthenticated',
            'message' => 'Unauthenticated'
        ],
        ModelNotFoundException::class => [
            'status' => Response::HTTP_NOT_FOUND,
            'error' => 'not_found',
            'message' => 'Resource not found'
        ],
        QueryException::class => [
            'status' => Response::HTTP_CONFLICT,
            'error' => 'database_error',
            'message' => 'Database operation failed'
        ],
        InvalidPassword::class => [
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'error' => 'invalid_password',
            'message' => 'Invalid password'
        ],
        UsersDomainException::class => [
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'error' => 'domain_error',
            'message' => 'Business rule violation'
        ],
        CellphoneDomainException::class => [
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'error' => 'domain_error', 
            'message' => 'Business rule violation'
        ],
        \RuntimeException::class => [
            'status' => Response::HTTP_BAD_REQUEST,
            'error' => 'runtime_error',
            'message' => 'Operation failed'
        ]
    ];

    public static function handle(\Throwable $e): JsonResponse
    {
        $debug = config('app.debug');
        $exceptionClass = get_class($e);
        
        // Buscar en el mapeo de excepciones
        foreach (self::ERROR_MAP as $exceptionType => $config) {
            if ($e instanceof $exceptionType) {
                return self::buildResponse($e, $config, $debug);
            }
        }

        // Para excepciones no mapeadas
        return self::buildGenericResponse($e, $debug);
    }

    private static function buildResponse(\Throwable $e, array $config, bool $debug): JsonResponse
    {
        $response = [
            'error' => $config['error'],
            'message' => $debug ? $e->getMessage() : $config['message']
        ];

        // Agregar detalles específicos para ciertas excepciones
        if ($e instanceof ValidationException && method_exists($e, 'errors')) {
            $response['errors'] = $e->errors();
        }

        if ($e instanceof QueryException && $debug) {
            $response['sql'] = $e->getSql();
            $response['bindings'] = $e->getBindings();
        }

        if ($debug) {
            $response['exception'] = get_class($e);
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
        }

        return response()->json($response, $config['status']);
    }

    private static function buildGenericResponse(\Throwable $e, bool $debug): JsonResponse
    {
        $response = [
            'error' => 'internal_error',
            'message' => $debug ? $e->getMessage() : 'Internal server error'
        ];

        if ($debug) {
            $response['exception'] = get_class($e);
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
            $response['trace'] = $e->getTrace();
        }

        return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Método helper para respuestas de éxito
    public static function successResponse(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    // Método helper para respuestas de error personalizadas
    public static function errorResponse(string $error, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => $error,
            'message' => $message
        ], $status);
    }
}
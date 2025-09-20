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

final class ProblemDetailsExceptionHandler
{
    private const ERROR_MAP = [
        ValidationException::class => [
            'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
            'title' => 'Invalid Input',
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
        AuthenticationException::class => [
            'type' => 'https://tools.ietf.org/html/rfc7235#section-3.1',
            'title' => 'Unauthorized',
            'status' => Response::HTTP_UNAUTHORIZED,
        ],
        ModelNotFoundException::class => [
            'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.4',
            'title' => 'Not Found',
            'status' => Response::HTTP_NOT_FOUND,
        ],
        QueryException::class => [
            'type' => 'https://tools.ietf.org/html/rfc7231#section-6.6.1',
            'title' => 'Database Error',
            'status' => Response::HTTP_CONFLICT,
        ],
        InvalidPassword::class => [
            'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
            'title' => 'Invalid Password',
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
        UsersDomainException::class => [
            'type' => 'https://tools.ietf.org/html/rfc4918#section-11.2',
            'title' => 'Business Rule Violation',
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
        CellphoneDomainException::class => [
            'type' => 'https://tools.ietf.org/html/rfc4918#section-11.2',
            'title' => 'Business Rule Violation',
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
        \RuntimeException::class => [
            'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
            'title' => 'Bad Request',
            'status' => Response::HTTP_BAD_REQUEST,
        ]
    ];

    public static function handle(\Throwable $e): JsonResponse
    {
        $debug = config('app.debug');
        $exceptionClass = get_class($e);
        
        foreach (self::ERROR_MAP as $exceptionType => $config) {
            if ($e instanceof $exceptionType) {
                return self::buildProblemDetailsResponse($e, $config, $debug);
            }
        }

        return self::buildGenericProblemDetailsResponse($e, $debug);
    }

    private static function buildProblemDetailsResponse(\Throwable $e, array $config, bool $debug): JsonResponse
    {
        $problemDetails = [
            'type' => $config['type'],
            'title' => $config['title'],
            'status' => $config['status'],
            'detail' => $debug ? $e->getMessage() : self::getSafeDetail($e, $config['status']),
            'instance' => request()->getRequestUri(),
        ];

        // Agregar detalles específicos para ciertas excepciones
        if ($e instanceof ValidationException && method_exists($e, 'errors')) {
            $problemDetails['invalid_params'] = $e->errors();
        }

        if ($e instanceof QueryException && $debug) {
            $problemDetails['sql'] = $e->getSql();
            $problemDetails['bindings'] = $e->getBindings();
        }

        if ($debug) {
            $problemDetails['exception'] = get_class($e);
            $problemDetails['file'] = $e->getFile();
            $problemDetails['line'] = $e->getLine();
            $problemDetails['trace'] = $e->getTraceAsString();
        }

        return response()->json($problemDetails, $config['status'], [
            'Content-Type' => 'application/problem+json'
        ]);
    }

    private static function buildGenericProblemDetailsResponse(\Throwable $e, bool $debug): JsonResponse
    {
        $problemDetails = [
            'type' => 'https://tools.ietf.org/html/rfc7231#section-6.6.1',
            'title' => 'Internal Server Error',
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'detail' => $debug ? $e->getMessage() : 'An unexpected error occurred',
            'instance' => request()->getRequestUri(),
        ];

        if ($debug) {
            $problemDetails['exception'] = get_class($e);
            $problemDetails['file'] = $e->getFile();
            $problemDetails['line'] = $e->getLine();
            $problemDetails['trace'] = $e->getTraceAsString();
        }

        return response()->json($problemDetails, Response::HTTP_INTERNAL_SERVER_ERROR, [
            'Content-Type' => 'application/problem+json'
        ]);
    }

    private static function getSafeDetail(\Throwable $e, int $status): string
    {
        return match($status) {
            Response::HTTP_NOT_FOUND => 'The requested resource was not found',
            Response::HTTP_UNAUTHORIZED => 'Authentication required',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'The request contains invalid parameters',
            Response::HTTP_CONFLICT => 'A conflict occurred with the current state',
            default => 'An error occurred while processing your request'
        };
    }

    public static function createProblemDetails(
        string $type,
        string $title,
        int $status,
        string $detail,
        array $extensions = []
    ): JsonResponse {
        $problemDetails = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => request()->getRequestUri(),
        ] + $extensions;

        return response()->json($problemDetails, $status, [
            'Content-Type' => 'application/problem+json'
        ]);
    }
}
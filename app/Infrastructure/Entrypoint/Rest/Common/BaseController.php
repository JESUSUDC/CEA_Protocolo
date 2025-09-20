<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Common;

use App\Infrastructure\Entrypoint\Rest\Common\ErrorHandler\ApiExceptionHandler;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Infrastructure\Entrypoint\Rest\Common\ErrorHandler\ProblemDetailsExceptionHandler;
use Illuminate\Http\Response;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function handleException(\Throwable $e, string $context = ''): JsonResponse
    {
        if (!empty($context)) {
            Log::error("[$context] " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        return ProblemDetailsExceptionHandler::handle($e);
    }

    protected function handleValidation(callable $callback, string $context = ''): JsonResponse
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            if (!empty($context)) {
                Log::warning("[$context] Validation failed: " . $e->getMessage());
            }
            return ProblemDetailsExceptionHandler::handle($e);
        } catch (\Throwable $e) {
            return $this->handleException($e, $context);
        }
    }

    protected function validateRequest(Request $request, array $rules, string $context = ''): array
    {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            if (!empty($context)) {
                Log::warning("[$context] Validation failed");
            }
            throw $e;
        }
    }

    protected function successResponse(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    protected function notFoundResponse(string $detail = 'Resource not found'): JsonResponse
    {
        return ProblemDetailsExceptionHandler::createProblemDetails(
            'https://tools.ietf.org/html/rfc7231#section-6.5.4',
            'Not Found',
            Response::HTTP_NOT_FOUND,
            $detail
        );
    }

    protected function conflictResponse(string $detail = 'Conflict occurred'): JsonResponse
    {
        return ProblemDetailsExceptionHandler::createProblemDetails(
            'https://tools.ietf.org/html/rfc7231#section-6.5.8',
            'Conflict',
            Response::HTTP_CONFLICT,
            $detail
        );
    }

    protected function badRequestResponse(string $detail = 'Bad request'): JsonResponse
    {
        return ProblemDetailsExceptionHandler::createProblemDetails(
            'https://tools.ietf.org/html/rfc7231#section-6.5.1',
            'Bad Request',
            Response::HTTP_BAD_REQUEST,
            $detail
        );
    }
}
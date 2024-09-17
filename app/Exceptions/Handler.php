<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            return response()->json([
                'error' => 'Record not found.'
            ], 404);
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->wantsJson()) {
            // Handle 404 errors
            if ($exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => 'Resource not found',
                    'message' => $exception->getMessage()
                ], 404);
            }

            // Handle validation exceptions
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'error' => 'Validation error',
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors()
                ], 422);
            }

            // Handle other exceptions
            return response()->json([
                'error' => 'Server Error',
                'message' => $exception->getMessage()
            ], 500);
        }

        return parent::render($request, $exception);
    }
}

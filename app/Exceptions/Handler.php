<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * Reportable exceptions
     */
    protected $dontReport = [
        //
    ];

    /**
     * Flashable inputs on validation errors
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception handling callbacks
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error($e); // bütün xətaları loglayır
        });
    }

    /**
     * Render exceptions as JSON for API
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {

            $status = 500;
            $message = $exception->getMessage();

            if ($exception instanceof ModelNotFoundException) {
                $status = 404;
                $message = 'Resurs tapılmadı';
            } elseif ($exception instanceof AuthenticationException) {
                $status = 401;
                $message = 'Giriş tələb olunur';
            } elseif ($exception instanceof ValidationException) {
                $status = 422;
                $message = $exception->errors();
            }

            Log::error($exception);

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], $status);
        }

        return parent::render($request, $exception);
    }
}

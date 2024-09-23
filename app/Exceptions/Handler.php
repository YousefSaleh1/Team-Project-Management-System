<?php

namespace App\Exceptions;

use CodingPartners\AutoController\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
            //
        });
    }

    /**
     * This is a custom exception handler method that renders responses for exceptions
     * @param mixed $request: Represents the HTTP request that caused the exception.
     * @param \Throwable $exception : Represents the caught exception that occurred during the request processing.
     * @return mixed|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Check if the exception is an instance of ModelNotFoundException
        if ($exception instanceof ModelNotFoundException) {
            return $this->errorResponse(null, 'Not Found', 404);
        }

        // If the exception is not a ModelNotFoundException, call the parent render method
        return parent::render($request, $exception);
    }
}

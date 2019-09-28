<?php

namespace App\Exceptions;

use Exception;
use App\Helpers\ErrorHelpers;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Exceptions\HttpResponseException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        HttpResponseException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof HttpResponseException) {
            return parent::render($request, $exception);
        } else {
            if (method_exists($exception, 'getStatusCode')) {
                if ($exception->getStatusCode() == 404) {
                    return app(ErrorHelpers::class)->throw(3002, $exception, false);
                }
            }
            if ($exception instanceof \GuzzleHttp\Exception\GuzzleException) {
                if ($exception->getResponse()->getStatusCode() === 404) {
                    return app(ErrorHelpers::class)->throw(3001, $exception, false);
                } else {
                    return app(ErrorHelpers::class)->throw(2002, $exception, false);
                }
            }

            return app(ErrorHelpers::class)->throw(2001, $exception, false);
        }
    }
}

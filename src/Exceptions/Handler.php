<?php

namespace Fligno\StarterKit\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

/**
 * Class Handler
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-09
 *
 * Extended the render() and unauthenticated() errors to output a unified format based on ExtendedResponse
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(static function (Throwable $e) {
            //
        });
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @return Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($e instanceof ModelNotFoundException) {
            // return your custom response
            return customResponse()
              ->data([])
              ->message('The identifier you are querying does not exist')
              ->slug('no_query_result')
              ->failed(404)
              ->generate();
        }

        if ($e instanceof AuthorizationException) {
            return customResponse()
              ->data([])
              ->message('You do not have right to access this resource')
              ->slug('forbidden_request')
              ->failed(403)
              ->generate();
        }

        return parent::render($request, $e);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function unauthenticated($request, AuthenticationException $exception): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        return customResponse()
          ->data([])
          ->message('You do not have valid authentication token')
          ->slug('missing_bearer_token')
          ->failed(401)
          ->generate();
    }
}

<?php

namespace Fligno\StarterKit\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use JetBrains\PhpStorm\Pure;

/**
 * Class UnauthorizedException
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 *
 * @since  2021-11-09
 */
class UnauthorizedException extends Exception
{
    #[Pure]
    public function __construct($message = '')
    {
        parent::__construct($message, null);
    }

    public function render($request): JsonResponse
    {
        return customResponse()
            ->data([])
            ->message($this->getMessage() ?: 'You do not have the necessary permission to access this resource.')
            ->failed(403)
            ->generate();
    }
}

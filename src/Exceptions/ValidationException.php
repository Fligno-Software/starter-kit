<?php

namespace Fligno\StarterKit\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException as BaseException;

/**
 * Class ValidationException
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 *
 * @since  2021-11-09
 */
class ValidationException extends BaseException
{
    public function render($request): JsonResponse
    {
        $slug = Str::slug($this->validator->errors()->first(), '_');

        return customResponse()
            ->data([])
            ->slug($slug)
            ->message(Arr::flatten($this->validator->errors()->messages()))
            ->failed(422)
            ->generate();
    }
}

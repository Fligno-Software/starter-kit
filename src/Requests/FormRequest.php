<?php
namespace Fligno\StarterKit\Requests;

use Fligno\StarterKit\Exceptions\UnauthorizedException;
use Fligno\StarterKit\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as BaseRequest;

/**
 * Class FormRequest
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since  2021-11-10
 */
class FormRequest extends BaseRequest
{
    /**
     * Override default failedAuthorization method.
     *
     * @throws UnauthorizedException
     */
    protected function failedAuthorization(): void
    {
        throw new UnauthorizedException();
    }

    /**
     * Override default failedValidation method.
     *
     * @param  Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException($validator);
    }
}

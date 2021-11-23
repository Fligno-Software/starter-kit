<?php

namespace Fligno\StarterKit\Rules;

use Illuminate\Contracts\Validation\Rule;
use Str;

/**
 * Class MustBeSnakeCase
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-19
 */
class MustBeSnakeCase implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return Str::of($value)->snake()->exactly($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a lowercase string with underscore as separator instead of spaces.';
    }
}

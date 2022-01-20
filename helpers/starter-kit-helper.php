<?php

/**
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-09
 */


use Fligno\StarterKit\ExtendedResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

if (! function_exists('customResponse')) {
    /**
     * @return ExtendedResponse
     */
    function customResponse(): ExtendedResponse
    {
        return resolve('extended-response');
    }
}

if (! function_exists('custom_response')) {
    /**
     * @return ExtendedResponse
     */
    function custom_response(): ExtendedResponse
    {
        return customResponse();
    }
}

if (!function_exists('array_filter_recursive')) {
    /**
     * @param array $arr
     * @param bool $accept_boolean
     * @param bool $accept_null
     * @param bool $accept_0
     * @return array
     */
    function array_filter_recursive(array $arr, bool $accept_boolean = FALSE, bool $accept_null = FALSE, bool $accept_0 = FALSE): array
    {
        $result = [];
        foreach ($arr as $key => $value) {
            if (($accept_boolean && is_bool($value)) || ($accept_0 && is_numeric($value) && (int)$value === 0) || empty($value) === FALSE || ($accept_null && is_null($value))) {
                if (is_array($value)) {
                    $result[$key] = array_filter_recursive($value, $accept_boolean, $accept_null, $accept_0);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}

if (!function_exists('is_request_instance')) {

    /**
     * @param $request
     * @return bool
     */
    function is_request_instance($request): bool
    {
        return is_subclass_of($request, Request::class);
    }
}

if (!function_exists('request_or_array_has')) {
    /**
     * Check if the Request or associative array has a specific key.
     *
     * @param array|Request $request
     * @param string $key
     * @param bool|null $is_exact
     * @return bool
     */
    function request_or_array_has(array|Request $request, string $key = '', ?bool $is_exact = true): bool
    {
        if (is_array($request) && (empty($request) || Arr::isAssoc($request))) {
            if ($is_exact) {
                return Arr::has($request, $key);
            }

            return (bool)preg_grep("/$key/", array_keys($request));

        }

        if (is_subclass_of($request, Request::class)) {
            if ($is_exact) {
                return $request->has($key);
            }

            return (bool)preg_grep("/$key/", $request->keys());
        }

        return FALSE;
    }
}

if (!function_exists('request_or_array_get')) {
    /**
     * Get a value from Request or associative array using a string key.
     *
     * @param array|Request $request
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    function request_or_array_get(array|Request $request, string $key, mixed $default = null): mixed
    {
        if (request_or_array_has($request, $key)) {
            if (is_array($request)) {
                return $request[$key];
            }

            return $request->$key;
        }

        return $default;
    }
}

if (!function_exists('is_request_or_array_filled')) {
    /**
     * Check if a key exists and is not empty on a Request or associative array.
     *
     * @param array|Request $request
     * @param string $key
     * @return bool
     */
    function is_request_or_array_filled(array|Request $request, string $key): bool
    {
        if (request_or_array_has($request, $key)) {
            if (is_array($request)) {
                return Arr::isFilled($request, $key);
            }

            return $request->filled($key);
        }

        return FALSE;
    }
}

if (!function_exists('is_eloquent_model')) {
    /**
     * Determine if the class using the trait is a subclass of Eloquent Model.
     *
     * @param mixed $object_or_class
     * @return bool
     */
    function is_eloquent_model(mixed $object_or_class): bool
    {
        return is_subclass_of($object_or_class, Model::class);
    }
}

if (!function_exists('get_class_name_from_object')) {
    /**
     * @param mixed $object_or_class
     * @return mixed
     */
    function get_class_name_from_object(mixed $object_or_class): mixed
    {
        return is_object($object_or_class) ? get_class($object_or_class) : $object_or_class;
    }
}

/***** COLLECTION-RELATED *****/

if (! function_exists('collection_decode')) {
    /**
     * Decode a string to a Collection instance.
     *
     * @param string|null $collection
     * @return Collection|string|null
     * @throws JsonException
     */
    function collection_decode(?string $collection): string|Collection|null
    {
        if ($collection) {
            $temp = json_decode($collection, true, 512, JSON_THROW_ON_ERROR);

            if (json_last_error() === JSON_ERROR_NONE) {
                return collect($temp);
            }
        }

        return $collection;
    }
}

if (! function_exists('collection_encode')) {
    /**
     * Decode a string to a Collection instance.
     *
     * @param Collection|null $collection
     * @return false|Collection|string|null
     * @throws JsonException
     */
    function collection_encode(?Collection $collection): bool|string|Collection|null
    {
        if ($collection) {
            $temp = json_encode($collection, JSON_THROW_ON_ERROR);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $temp;
            }
        }

        return $collection;
    }
}

<?php

namespace Fligno\StarterKit\Services;

use Fligno\StarterKit\Traits\UsesDataParsingTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Class CustomResponse
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 *
 * @since  2021-11-19
 *
 * Usage
 *  This is a simple Response Class that allows you to method-chain
 *  The creation of response as well as creating a unified response format
 *  The end of your chain must always end with the generate() function
 */
class CustomResponse
{
    use UsesDataParsingTrait;

    /**
     * @var string|null
     */
    protected ?string $message = null;

    /**
     * @var mixed|null
     */
    protected mixed $data = null;

    /**
     * @var int
     */
    protected int $code = 200;

    /**
     * @var bool
     */
    protected bool $success = true;

    /**
     * @var string|null
     */
    protected string|null $slug = null;

    /**
     * @var array
     */
    protected array $pagination = [];

    /**
     * ExtendedResponse constructor.
     *
     * @param string|null $message
     * @param mixed $data
     * @param int $code
     */
    public function __construct(?string $message = null, mixed $data = null, int $code = 200)
    {
        $this
            ->message($message)
            ->data($data)
            ->code($code);
    }

    /***** HTTP CODE RELATED *****/

    /**
     * Set status code
     *
     * @param  int  $code
     * @return $this
     */
    public function code(int $code): CustomResponse
    {
        $this->code = $code;

        if ($code >= 400) {
            $this->success = false;
        }

        return $this;
    }

    /**
     * Generic success code
     *
     * @param  int  $code
     * @return $this
     */
    public function success(int $code = 200): CustomResponse
    {
        return $this->code($code);
    }

    /**
     * Generic failure code
     *
     * @param  int  $code
     * @return $this
     */
    public function failed(int $code = 400): CustomResponse
    {
        return $this->code($code);
    }

    /**
     * Lacks authentication method
     * If auth middleware is not activated by default
     *
     * @return $this
     */
    public function unauthorized(int $code = 401): CustomResponse
    {
        return $this->code($code);
    }

    /**
     * User permission specific errors
     *
     * @return $this
     */
    public function forbidden(int $code = 403): CustomResponse
    {
        return $this->code($code);
    }

    /**
     * Model search related errors
     *
     * @return $this
     */
    public function notFound(int $code = 404): CustomResponse
    {
        return $this->code($code);
    }

    /***** HTTP MESSAGE RELATED *****/

    /**
     * Set a custom slug
     *
     * @param string $title
     * @param string[] $dictionary
     * @return $this
     */
    public function slug(string $title, array $dictionary = []): CustomResponse
    {
        $title = Str::after($title, '::');

        $default_dictionary = ['@' => 'at', '/' => ' ', '.' => ' '];
        $dictionary = array_merge($default_dictionary, $dictionary);

        $this->slug = Str::slug(title: $title, separator: '_', dictionary: $dictionary);

        return $this;
    }

    /**
     * Set message
     *
     * @param string|null $message
     * @param array $replace
     * @return $this
     */
    public function message(string|null $message, array $replace = []): CustomResponse
    {
        if ($message) {
            if (! $this->slug) {
                $this->slug(title: $message);
            }

            // Translate the message
            $this->message = __(key: $message, replace: $replace, locale: App::getLocale());
        }

        return $this;
    }

    /**
     * Set data
     *
     * @param  mixed  $value
     * @return $this
     */
    public function data(mixed $value = null): CustomResponse
    {
        if ($value instanceof ResourceCollection) {
            $pagination = $value->response(request())->getData(true);
            $data = $pagination['data'];
            unset($pagination['data']);

            // separate them on two different array keys to create uniformity
            $this->pagination = $pagination;
            $this->data = $data;
        } elseif ($value instanceof AbstractPaginator) { // for Paginator and LengthAwarePaginator
            // convert pagination to array
            $pagination = $value->toArray();
            $data = $pagination['data'];
            unset($pagination['data']);

            // separate them on two different array keys to create uniformity
            $this->pagination = $pagination;
            $this->data = $data;
        } elseif ($value instanceof JsonResource) {
            $this->data = $value->toArray(request());
        } else {
            $this->data = $this->parse($value);
        }

        return $this;
    }

    /**
     * Generate response
     *
     * @return JsonResponse
     */
    public function generate(): JsonResponse
    {
        $data = collect([
            'success' => $this->success,
            'code' => $this->code,
            'locale' => App::getLocale(),
            'slug' => $this->slug,
            'message' => $this->message,
        ]);

        // Add the data or errors based on status code
        if (! empty($this->data)) {
            if ($this->code >= 400) {
                $data->put('errors', $this->data);
            } else {
                $data->put('data', $this->data);
            }
        }

        // Add pagination if not empty
        if (! empty($this->pagination)) {
            $data->put('pagination', $this->pagination);
        }

        return response()->json($data->toArray(), $this->code);
    }
}

<?php

namespace Fligno\StarterKit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class ExtendedResponse
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since  2021-11-19
 *
 * Usage
 *  This is a simple Response Class that allows you to method-chain
 *  The creation of response as well as creating a unified response format
 *  The end of your chain must always end with the generate() function
 */
class ExtendedResponse
{
    /**
     * @var Paginator|LengthAwarePaginator|AnonymousResourceCollection|Collection|Model|array|null
     */
    protected Paginator|LengthAwarePaginator|AnonymousResourceCollection|Collection|Model|array|null $data = [];

    /**
     * @var int
     */
    protected int $code = 200;

    /**
     * @var bool
     */
    protected bool $success = true;

    /**
     * @var array
     */
    protected array $message = [];

    /**
     * @var string
     */
    protected string $slug = '';

    /**
     * @var array
     */
    protected array $pagination = [];

    /**
     * ExtendedResponse constructor.
     *
     * @param Paginator|LengthAwarePaginator|AnonymousResourceCollection|Collection|Model|array|null $data
     * @param array|string|null                                                                      $message
     */
    public function __construct(
        Paginator|LengthAwarePaginator|AnonymousResourceCollection|Collection|Model|array $data = null,
        array|string $message = null
    ) {
        if (empty($data) === false) {
            $this->data($data);
        }

        if (empty($message) === false) {
            $this->message($message);
        }
    }

    /**
     * Set status code
     *
     * @param  int $code
     * @return $this
     */
    public function code(int $code): ExtendedResponse
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
     * @param  int $code
     * @return $this
     */
    public function success(int $code = 200): ExtendedResponse
    {
        $this->code = $code;
        $this->success = true;

        return $this;
    }

    /**
     * Generic failure code
     *
     * @param  int $code
     * @return $this
     */
    public function failed(int $code = 400): ExtendedResponse
    {
        $this->code = $code;
        $this->success = false;

        return $this;
    }


    /**
     * Lacks authentication method
     * If auth middleware is not activated by default
     *
     * @return $this
     */
    public function unauthorized(): ExtendedResponse
    {
        $this->code = 401;
        $this->success = false;

        return $this;
    }

    /**
     * User permission specific errors
     *
     * @return $this
     */
    public function forbidden(): ExtendedResponse
    {
        $this->code = 403;
        $this->success = false;

        return $this;
    }

    /**
     * Model search related errors
     *
     * @return $this
     */
    public function notFound(): ExtendedResponse
    {
        $this->code = 404;
        $this->success = false;

        return $this;
    }

    /**
     * Set a custom slug
     *
     * @param  string $value
     * @return $this
     */
    public function slug(string $value): ExtendedResponse
    {
        $this->slug = $value;

        return $this;
    }

    /**
     * Set message
     *
     * @param  array|string|null $value
     * @return $this
     */
    public function message(array|string|null $value): ExtendedResponse
    {
        if (is_string($value)) {
            $value = [$value];
        } elseif (is_null($value)) {
            $value = [];
        }

        // set slug too
        if (empty($this->slug)) {
            $this->slug = Str::slug($value[0], '_');
        }

        $this->message = $this->translateMessage($value);

        return $this;
    }

    /**
     * Implement a message translator based on slug given
     *
     * @param  $fallback
     * @return mixed
     */
    protected function translateMessage($fallback): mixed
    {
        return $fallback;
    }

    /**
     * Set data
     *
     * @param  Paginator|LengthAwarePaginator|AnonymousResourceCollection|Collection|Model|array|null $value
     * @return $this
     */
    public function data(
        Paginator|LengthAwarePaginator|AnonymousResourceCollection|Collection|Model|array $value = null
    ): ExtendedResponse {
        if ($value instanceof ResourceCollection) {
            $pagination = $value->response(request())->getData(true);
            $data = $pagination['data'];
            unset($pagination['data']);

            // separate them on two different array keys to create uniformity
            $this->pagination = $pagination;
            $this->data = $data;
        } elseif ($value instanceof Paginator || $value instanceof LengthAwarePaginator) {
            // convert pagination to array
            $pagination = $value->toArray();
            $data = $pagination['data'];
            unset($pagination['data']);

            // separate them on two different array keys to create uniformity
            $this->pagination = $pagination;
            $this->data = $data;
        } elseif ($value instanceof Collection || $value instanceof Model) {
            $this->data = $value->toArray();
        } else {
            $this->data = $value;
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
        return $this->generateResponse();
    }

    /**
     * Generate response
     *
     * @return JsonResponse
     */
    protected function generateResponse(): JsonResponse
    {
        return response()->json(
            [
                'success'     => $this->success,
                'code'        => $this->code,
                'slug'        => $this->slug,
                'message'     => $this->message,
                'data'        => $this->data,
                'pagination'  => $this->pagination,
            ],
            $this->code
        );
    }
}

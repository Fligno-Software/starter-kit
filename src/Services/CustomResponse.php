<?php

namespace Fligno\StarterKit\Services;

use Fligno\StarterKit\Traits\UsesDataParsingTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
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
     * @var mixed
     */
    protected mixed $data = [];

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
     * @param  mixed  $data
     * @param  array|string|null  $message
     */
    public function __construct(mixed $data = null, array|string $message = null)
    {
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
        $this->code = $code;
        $this->success = true;

        return $this;
    }

    /**
     * Generic failure code
     *
     * @param  int  $code
     * @return $this
     */
    public function failed(int $code = 400): CustomResponse
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
    public function unauthorized(): CustomResponse
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
    public function forbidden(): CustomResponse
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
    public function notFound(): CustomResponse
    {
        $this->code = 404;
        $this->success = false;

        return $this;
    }

    /**
     * Set a custom slug
     *
     * @param  string  $value
     * @return $this
     */
    public function slug(string $value): CustomResponse
    {
        $this->slug = $value;

        return $this;
    }

    /**
     * Set message
     *
     * @param  array|string|null  $value
     * @return $this
     */
    public function message(array|string|null $value): CustomResponse
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
     * @param    $fallback
     * @return mixed
     */
    protected function translateMessage($fallback): mixed
    {
        return $fallback;
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
        return $this->generateResponse();
    }

    /**
     * Generate response
     *
     * @return JsonResponse
     */
    protected function generateResponse(): JsonResponse
    {
        $data = collect([
            'success' => $this->success,
            'code' => $this->code,
            'slug' => $this->slug,
            'message' => $this->message,
            'pagination' => $this->pagination,
        ]);

        if ($this->code >= 400) {
            $data->put('errors', $this->data);
        } else {
            $data->put('data', $this->data);
        }

        return response()->json($data->toArray(), $this->code);
    }
}

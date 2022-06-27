<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Abstracts\BaseJsonSerializable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;

/**
 * Trait NeedsDataParsingTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesDataParsingTrait
{
    /**
     * @param array $response
     * @param string|null $key
     * @return array
     */
    public function parseArray(array $response, ?string $key = null): array
    {
        return $key && isset($response[$key]) ? Arr::wrap($response[$key]) : $response;
    }

    /**
     * @param Response $response
     * @param string|null $key
     * @return array
     */
    public function parseResponse(Response $response, ?string $key = null): array
    {
        if ($response->ok() && $array = $response->json($key)) {
            return $this->parse($array);
        }

        return [];
    }

    /**
     * @param Request $response
     * @param string|null $key
     * @return array
     */
    public function parseRequest(Request $response, ?string $key = null): array
    {
        $response = $response instanceof FormRequest ? $response->validated() : $response->all();

        return $this->parse($response, $key);
    }

    /**
     * @param Collection $response
     * @param string|null $key
     * @return array
     */
    public function parseCollection(Collection $response, ?string $key = null): array
    {
        return $key && $response->has($key) ? $this->parse($response->get($key)) : $response->toArray();
    }

    /**
     * @param BaseJsonSerializable $response
     * @param string|null $key
     * @return array
     */
    public function parseBaseJsonSerializable(BaseJsonSerializable $response, ?string $key = null): array
    {
        return $this->parse($response->toArray(), $key);
    }

    /**
     * @param Model $response
     * @param string|null $key
     * @return array
     */
    public function parseModel(Model $response, ?string $key = null): array
    {
        return $key && isset($response->$key) ? $this->parse($response->$key) : $response->toArray();
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return array
     */
    public function parse(mixed $data = [], ?string $key = null): array
    {
        if (is_array($data)) {
            return $this->parseArray($data, $key);
        }

        if ($data instanceof self) {
            return $this->parseBaseJsonSerializable($data, $key);
        }

        if ($data instanceof Response) {
            return $this->parseResponse($data, $key);
        }

        if ($data instanceof Request) {
            return $this->parseRequest($data, $key);
        }

        if ($data instanceof Collection) {
            return $this->parseCollection($data, $key);
        }

        if ($data instanceof Model) {
            return $this->parseModel($data, $key);
        }

        try {
            if (is_object($data) && ($class = (new ReflectionClass($data))->getShortName()) && method_exists($this, $method = 'parse' . $class)) {
                return $this->$method($data, $key);
            }
        } catch (ReflectionException) {
            return [];
        }

        return [];
    }
}

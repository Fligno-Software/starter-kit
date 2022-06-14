<?php

namespace Fligno\StarterKit\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;
use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Class BaseJsonSerializable
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
abstract class BaseJsonSerializable implements JsonSerializable
{
    /**
     * @var mixed
     */
    protected mixed $original_data;

    /**
     * @param mixed $data
     * @param string|null $key
     * @throws ReflectionException
     */
    public function __construct(mixed $data = [], ?string $key = null) {
        $this->setOriginalData($data, $key);
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return static
     * @throws ReflectionException
     */
    public static function from(mixed $data = [], ?string $key = null): static {
        return new static($data, $key);
    }

    /***** PARSE DIFFERENT DATA SOURCE *****/

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
     * @throws ReflectionException
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
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    public function parseCollection(Collection $response, ?string $key = null): array
    {
        return $key && $response->has($key) ? $this->parse($response->get($key)) : $response->toArray();
    }

    /**
     * @param BaseJsonSerializable $response
     * @param string|null $key
     * @return array
     * @throws ReflectionException
     */
    public function parseBaseJsonSerializable(BaseJsonSerializable $response, ?string $key = null): array
    {
        return $this->parse($response->toArray(), $key);
    }

    /**
     * @param Model $response
     * @param string|null $key
     * @return array
     * @throws ReflectionException
     */
    public function parseModel(Model $response, ?string $key = null): array
    {
        return $key && isset($response->$key) ? $this->parse($response->$key) : $response->toArray();
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return array
     * @throws ReflectionException
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

        if (is_object($data) && ($class = (new ReflectionClass($data))->getShortName()) && method_exists($this, $method = 'parse' . $class)) {
            return $this->$method($data, $key);
        }

        return [];
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return void
     * @throws ReflectionException
     */
    public function mergeDataToFields(mixed $data = [], ?string $key = null): void {
        $data = $this->parse($data, $key);

        if (Arr::isAssoc($data)) {
            $this->setFields($data);
        }
    }

    /**
     * @param array $array
     * @return $this
     */
    protected function setFields(array $array): static
    {
        foreach (get_class_vars(static::class) as $key => $value) {
            if (Arr::has($array, $key)) {
                if (method_exists($this, $method = Str::of($key)->ucfirst()->prepend('set')->camel()->jsonSerialize())) {
                    $this->$method($array[$key]);
                }
                else {
                    $this->$key = $array[$key];
                }
            }
        }

        return $this;
    }

    /**
     * @param mixed $original_data
     * @param string|null $key
     * @throws ReflectionException
     */
    public function setOriginalData(mixed $original_data, ?string $key = null): void
    {
        $this->original_data = $original_data;

        $this->mergeDataToFields($original_data, $key);
    }

    /**
     * @return mixed
     */
    public function getOriginalData(): mixed
    {
        return $this->original_data;
    }

    /***** OVERRIDDEN FUNCTIONS *****/

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return static data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4
     */
    public function jsonSerialize(): static
    {
        return $this->performBeforeSerialize($this);
    }

    /**
     * @param BaseJsonSerializable $object
     * @return $this
     */
    public function performBeforeSerialize(self $object): static
    {
        return $object;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        try {
            return json_decode($this->__toString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link   https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        try {
            return json_encode($this, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '';
        }
    }

    /***** MORE FUNCTIONS *****/

    /**
     * @return Collection
     */
    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array
     */
    public function getFieldAliases(): array
    {
        return [];
    }

    /**
     * @return $this
     */
    public function aliased(): static
    {
        collect($this->getFieldAliases())->each(
            function ($value, $key) {
                if (isset($this->$key)) {
                    $temp = $this->$key;
                    $this->$value = is_object($temp) ? clone $temp : $temp;
                    unset($this->$key);
                }
            }
        );

        return $this;
    }

    /**
     * @param  Collection|string[]|string $fields
     * @return static
     */
    public function only(Collection|array|string $fields): static
    {
        $this->collect()->except(collect($fields))->each(
            function ($value, $key) {
                unset($this->$key);
            }
        );

        return $this;
    }

    /**
     * @param  Collection|string[]|string $fields
     * @return static
     */
    public function except(Collection|array|string $fields): static
    {
        $this->collect()->only(collect($fields))->each(
            function ($value, $key) {
                unset($this->$key);
            }
        );

        return $this;
    }

    /**
     * @return $this
     * @throws ReflectionException
     */
    public function clone(): static
    {
        return self::from($this);
    }

    /**
     * @return $this
     * @throws ReflectionException
     */
    public function clean(): static
    {
        return self::from($this->getOriginalData());
    }
}

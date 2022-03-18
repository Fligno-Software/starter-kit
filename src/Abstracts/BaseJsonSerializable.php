<?php

namespace Fligno\StarterKit\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use JsonException;
use JsonSerializable;

/**
 * Class BaseJsonSerializable
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
abstract class BaseJsonSerializable implements JsonSerializable
{
    /**
     * Class field aliases
     *
     * @var array
     */
    protected array $field_aliases = [];

    /**
     * @var BaseJsonSerializable|Response|Request|Collection|array
     */
    private BaseJsonSerializable|Response|Request|Collection|array $raw_data;

    /**
     * @param BaseJsonSerializable|Response|Request|Collection|Model|array|null $data
     * @param string|null $key
     */
    public function __construct(BaseJsonSerializable|Response|Request|Collection|Model|array|null $data = [], ?string $key = null)
    {
        $this->raw_data = $data;

        if($data instanceof Response) {
            $data = $this->parseResponse($data, $key);
        }
        elseif ($data instanceof Request) {
            $data = $this->parseRequest($data, $key);
        }
        elseif($data instanceof Collection) {
            $data = $this->parseCollection($data, $key);
        }
        elseif ($data instanceof self) {
            $data = $this->parseBaseJsonSerializable($data, $key);
        }
        elseif ($data instanceof Model) {
            $data = $this->parseModel($data, $key);
        }

        if (is_array($data) && Arr::isAssoc($data)) {
            $this->setFields($data);
        }
    }

    /**
     * @param BaseJsonSerializable|Response|Request|Collection|Model|array|null $data
     * @param string|null $key
     * @return static
     */
    public static function from(BaseJsonSerializable|Response|Request|Collection|Model|array|null $data = [], ?string $key = null): static
    {
        return new static($data, $key);
    }

    /***** PARSE DIFFERENT DATA SOURCE *****/

    /**
     * @param Response $response
     * @param string|null $key
     * @return array
     */
    public function parseResponse(Response $response, ?string $key = null): array
    {
        if($response->ok() && $array = $response->json($key)) {
            return $array;
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
        if ($response instanceof FormRequest) {
            return  $response->validated();
        }

        return $response->all();
    }

    /**
     * @param Collection $response
     * @param string|null $key
     * @return array
     */
    public function parseCollection(Collection $response, ?string $key = null): array
    {
        return $response->toArray();
    }

    /**
     * @param BaseJsonSerializable $response
     * @param string|null $key
     * @return array
     */
    public function parseBaseJsonSerializable(BaseJsonSerializable $response, ?string $key = null): array
    {
        return $response->toArray();
    }

    /**
     * @param Model $response
     * @param string|null $key
     * @return array
     */
    public function parseModel(Model $response, ?string $key = null): array
    {
        return $response->toArray();
    }

    /**
     * @param array $array
     * @return static
     */
    protected function setFields(array $array): static
    {
        foreach (get_class_vars(static::class) as $key=>$value){
            if(Arr::has($array, $key)) {
                if(method_exists($this, $method = Str::camel('set' . $key))) {
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
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return static data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    #[Pure] public function jsonSerialize(): static
    {
        return $this->performBeforeSerialize($this);
    }

    /**
     * @param static $object
     * @return static
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
        } catch (JsonException $e) {
            return [];
        }
    }

    /**
     * @return Collection
     */
    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return Collection
     */
    public function collectClassVars(): Collection
    {
        return collect(get_class_vars(static::class));
    }

    /**
     * @return Collection
     */
    public function collectObjectVars(): Collection
    {
        return collect(get_object_vars($this));
    }

    /**
     * @return array|BaseJsonSerializable|Response|Request|Collection
     */
    public function getRawData(): array|Response|Collection|Request|BaseJsonSerializable
    {
        return $this->raw_data;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        try {
            return json_encode($this, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return '';
        }
    }

    /**
     * @return static
     */
    public function aliased(): static
    {
        collect($this->field_aliases)->each(function ($value, $key) {
            if (isset($this->$key)) {
                $temp = $this->$key;
                $this->$value = is_object($temp) ? clone $temp : $temp;
                unset($this->$key);
            }
        });

        return $this;
    }

    /**
     * @param Collection|string[]|string $fields
     * @return static
     */
    public function only(Collection|array|string $fields): static
    {
        $this->collect()->except(collect($fields))->each(function ($value, $key){
            unset($this->$key);
        });

        return $this;
    }

    /**
     * @param Collection|string[]|string $fields
     * @return static
     */
    public function except(Collection|array|string $fields): static
    {
        $this->collect()->only(collect($fields))->each(function ($value, $key){
            unset($this->$key);
        });

        return $this;
    }

    /**
     * @return static
     */
    public function clone(): static
    {
        return self::from($this);
    }
}

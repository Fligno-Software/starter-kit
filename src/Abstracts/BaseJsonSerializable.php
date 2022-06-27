<?php

namespace Fligno\StarterKit\Abstracts;

use Fligno\StarterKit\Traits\UsesDataParsingTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonException;
use JsonSerializable;

/**
 * Class BaseJsonSerializable
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
abstract class BaseJsonSerializable implements JsonSerializable
{
    use UsesDataParsingTrait;

    /**
     * @var mixed
     */
    protected mixed $original_data;

    /**
     * @param mixed $data
     * @param string|null $key
     */
    public function __construct(mixed $data = [], ?string $key = null)
    {
        $this->setOriginalData($data, $key);
    }

    /**
     * @return array
     */
    public function getFieldAliases(): array
    {
        return [];
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return static
     */
    public static function from(mixed $data = [], ?string $key = null): static
    {
        return new static($data, $key);
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return void
     */
    public function mergeDataToFields(mixed $data = [], ?string $key = null): void
    {
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
                if (method_exists($this, $method = 'set' . $key)) {
                    $this->$method($array[$key]);
                } else {
                    $this->$key = $array[$key];
                }
            }
        }

        return $this;
    }

    /**
     * @param mixed $original_data
     * @param string|null $key
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
     * @return Collection
     */
    public function collectClassVars(): Collection
    {
        return collect(get_class_vars(static::class))->except('original_data');
    }

    /**
     * @return Collection
     */
    public function collectObjectVars(): Collection
    {
        return collect(get_object_vars($this))->except('original_data');
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
     */
    public function clone(): static
    {
        return self::from($this);
    }

    /**
     * @return $this
     */
    public function clean(): static
    {
        return self::from($this->getOriginalData());
    }
}

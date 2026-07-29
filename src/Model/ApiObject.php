<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

use ArrayAccess;
use JsonSerializable;

/**
 * @implements ArrayAccess<string, mixed>
 */
class ApiObject implements ArrayAccess, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(protected array $data) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function string(string $key): ?string
    {
        $value = $this->get($key);

        return is_string($value) ? $value : null;
    }

    public function int(string $key): ?int
    {
        $value = $this->get($key);

        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : null);
    }

    public function float(string $key): ?float
    {
        $value = $this->get($key);

        return is_float($value) || is_int($value) ? (float) $value : (is_numeric($value) ? (float) $value : null);
    }

    public function bool(string $key): ?bool
    {
        $value = $this->get($key);

        return is_bool($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function array(string $key): array
    {
        $value = $this->get($key);

        return is_array($value) ? $value : [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return is_string($offset) ? ($this->data[$offset] ?? null) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException(static::class.' is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException(static::class.' is immutable.');
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

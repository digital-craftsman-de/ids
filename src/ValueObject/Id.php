<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

abstract readonly class Id implements \Stringable
{
    // Construction

    final public function __construct(
        public string $value,
    ) {
        if (!uuid_is_valid($value)) {
            throw new Exception\InvalidId($value);
        }
    }

    public static function generateRandom(): static
    {
        return new static(uuid_create());
    }

    public static function fromString(string $id): static
    {
        return new static($id);
    }

    // Magic

    public function __toString(): string
    {
        return $this->value;
    }

    // Accessors

    /**
     * @param static $id
     */
    public function isEqualTo(self $id): bool
    {
        if (static::class !== $id::class) {
            throw new Exception\DifferentId($this, $id);
        }

        return $this->value === $id->value;
    }

    /**
     * @param static $id
     */
    public function isNotEqualTo(self $id): bool
    {
        if (static::class !== $id::class) {
            throw new Exception\DifferentId($this, $id);
        }

        return $this->value !== $id->value;
    }

    // Guards

    /**
     * @param static                  $id
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdNotEqual
     */
    public function mustBeEqualTo(
        self $id,
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->isNotEqualTo($id)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdNotEqual($this, $id);
        }
    }

    /**
     * @param static                  $id
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdEqual
     */
    public function mustNotBeEqualTo(
        self $id,
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->isEqualTo($id)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdEqual($this, $id);
        }
    }
}

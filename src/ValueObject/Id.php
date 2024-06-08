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

    public function isEqualTo(self $id): bool
    {
        return $this->value === $id->value;
    }

    public function isNotEqualTo(self $id): bool
    {
        return $this->value !== $id->value;
    }

    // Guards

    /**
     * @param ?callable(): \Throwable $exception
     *
     * @throws \Throwable
     * @throws Exception\IdNotEqual
     */
    public function mustBeEqualTo(
        self $id,
        ?callable $exception = null,
    ): void {
        if ($this->isNotEqualTo($id)) {
            throw $exception !== null
                ? $exception()
                : new Exception\IdNotEqual($this, $id);
        }
    }

    /**
     * @param ?callable(): \Throwable $exception
     *
     * @throws \Throwable
     * @throws Exception\IdEqual
     */
    public function mustNotBeEqualTo(
        self $id,
        ?callable $exception = null,
    ): void {
        if ($this->isEqualTo($id)) {
            throw $exception !== null
                ? $exception()
                : new Exception\IdEqual($this, $id);
        }
    }
}

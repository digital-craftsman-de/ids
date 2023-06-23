<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\IdEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\IdNotEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\InvalidId;

abstract readonly class Id implements \Stringable
{
    // Construction

    final public function __construct(
        public string $value,
    ) {
        if (!uuid_is_valid($value)) {
            throw new InvalidId($value);
        }
    }

    final public static function generateRandom(): static
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

    /** @throws IdNotEqual */
    public function mustBeEqualTo(self $id): void
    {
        if ($this->isNotEqualTo($id)) {
            throw new IdNotEqual($this, $id);
        }
    }

    /** @throws IdEqual */
    public function mustNotBeEqualTo(self $id): void
    {
        if ($this->isEqualTo($id)) {
            throw new IdEqual($this, $id);
        }
    }
}

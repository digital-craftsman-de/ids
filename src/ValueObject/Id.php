<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\IdEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\IdNotEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\InvalidId;
use Ramsey\Uuid\Uuid;

/** @psalm-immutable */
abstract class Id implements \Stringable
{
    // Construction

    final public function __construct(
        private string $value,
    ) {
        if (!Uuid::isValid($value)) {
            throw new InvalidId($value);
        }
    }

    final public static function generateRandom(): static
    {
        $id = Uuid::uuid4()->toString();

        return new static($id);
    }

    final public static function fromString(string $id): static
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

    /**
     * Comparison without strict to made with @see __toString(). We use this method so we don't use it in strict mode on
     * accident somewhere.
     *
     * @param array<int, static> $list
     */
    public function isExistingInList(array $list): bool
    {
        return in_array($this, $list, false);
    }

    /** @param array<int, static> $list */
    public function isNotExistingInList(array $list): bool
    {
        return !$this->isExistingInList($list);
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

<?php

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\IdEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\IdNotEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\InvalidId;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

abstract readonly class SymfonyId implements IdInterface, \Stringable
{
    // Construction
    public Uuid $value;

    final public function __construct(
        string $value,
    ) {
        if (!Uuid::isValid($value)) {
            throw new InvalidId($value);
        }

        $class = static::getClass();
        $this->value = new $class($value);
    }

    abstract protected static function getClass(): string;

    public static function generateRandom()
    {
        $class = static::getClass();
        return new static(new $class);
    }

    public static function fromString(string $id)
    {
        $class = static::getClass();
        return new static(new $class($id));
    }

    // Magic

    public function __toString(): string
    {
        return $this->value->toRfc4122();
    }

    // Accessors

    public function isEqualTo(IdInterface $id): bool
    {
        return $this->value->equals($id->value);
    }

    public function isNotEqualTo(IdInterface $id): bool
    {
        return !$this->value->equals($id->value);
    }

    // Guards

    public function mustBeEqualTo(IdInterface $id): void
    {
        if ($this->isNotEqualTo($id)) {
            throw new IdNotEqual($this, $id);
        }
    }

    public function mustNotBeEqualTo(IdInterface $id): void
    {
        if ($this->isEqualTo($id)) {
            throw new IdEqual($this, $id);
        }
    }

}

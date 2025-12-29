<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\SelfAwareNormalizers\Serializer\NullableStringDenormalizable;
use DigitalCraftsman\SelfAwareNormalizers\Serializer\NullableStringDenormalizableTrait;
use DigitalCraftsman\SelfAwareNormalizers\Serializer\StringNormalizable;

abstract readonly class Id implements \Stringable, StringNormalizable, NullableStringDenormalizable
{
    use NullableStringDenormalizableTrait;

    // -- Construction

    /**
     * @param non-empty-string $value
     */
    final public function __construct(
        public string $value,
    ) {
        if (!uuid_is_valid($value)) {
            throw new Exception\InvalidId($value);
        }
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion uuid_create will always create a non-empty-string
     */
    public static function generateRandom(): static
    {
        return new static(uuid_create());
    }

    /**
     * @param non-empty-string $id
     */
    public static function fromString(string $id): static
    {
        return new static($id);
    }

    // -- String normalizable

    /**
     * @param non-empty-string $data
     */
    #[\Override]
    public static function denormalize(string $data): static
    {
        return new static($data);
    }

    /**
     * @return non-empty-string
     */
    #[\Override]
    public function normalize(): string
    {
        return $this->value;
    }

    // -- Magic

    /**
     * @return non-empty-string
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    // -- Accessors

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @param static $id
     */
    public function isEqualTo(self $id): bool
    {
        return $this->value === $id->value;
    }

    /**
     * @param static $id
     */
    public function isNotEqualTo(self $id): bool
    {
        return $this->value !== $id->value;
    }

    // -- Guards

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

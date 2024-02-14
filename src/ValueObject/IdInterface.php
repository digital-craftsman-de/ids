<?php

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\IdEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\IdNotEqual;

/**
 * @property string $value
 */
interface IdInterface extends \Stringable
{
    public static function generateRandom(): static;

    public static function fromString(string $id): static;

    // Accessors

    public function isEqualTo(self $id): bool;

    public function isNotEqualTo(self $id): bool;

    // Guards

    /** @throws IdNotEqual */
    public function mustBeEqualTo(self $id): void;

    /** @throws IdEqual */
    public function mustNotBeEqualTo(self $id): void;
}

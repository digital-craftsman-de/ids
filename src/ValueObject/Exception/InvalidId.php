<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/** @psalm-immutable */
final class InvalidId extends \InvalidArgumentException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('The id %s is invalid', $id));
    }
}

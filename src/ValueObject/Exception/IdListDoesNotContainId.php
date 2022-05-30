<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

use DigitalCraftsman\Ids\ValueObject\Id;

/** @psalm-immutable */
final class IdListDoesNotContainId extends \DomainException
{
    public function __construct(Id $id)
    {
        parent::__construct(sprintf('List does not contain id %s', (string) $id));
    }
}

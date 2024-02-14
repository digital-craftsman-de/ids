<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

use DigitalCraftsman\Ids\ValueObject\IdInterface;

/** @psalm-immutable */
final class IdAlreadyInList extends \DomainException
{
    public function __construct(IdInterface $id)
    {
        parent::__construct(sprintf('The id %s is already in the list', (string) $id));
    }
}

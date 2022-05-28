<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

use DigitalCraftsman\Ids\ValueObject\BaseId;

/** @psalm-immutable */
final class IdEqual extends \DomainException
{
    public function __construct(BaseId $idA, BaseId $idB)
    {
        parent::__construct(sprintf(
            'The id %s is equal to id %s',
            (string) $idA,
            (string) $idB,
        ));
    }
}

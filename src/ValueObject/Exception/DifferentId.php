<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

use DigitalCraftsman\Ids\ValueObject\Id;

/** @psalm-immutable */
final class DifferentId extends \DomainException
{
    public function __construct(
        Id $id,
        Id $idToCompare,
    ) {
        parent::__construct(sprintf(
            'The id class %s is equal to id class %s',
            $id::class,
            $idToCompare::class,
        ));
    }
}

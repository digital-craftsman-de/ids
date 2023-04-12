<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/** @psalm-immutable */
final class IdListDoesContainEveryId extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The id list does contain every id of list');
    }
}

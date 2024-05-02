<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/** @psalm-immutable */
final class IdListIsEmpty extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Id list is empty');
    }
}

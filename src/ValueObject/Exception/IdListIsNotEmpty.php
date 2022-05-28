<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/** @psalm-immutable */
final class IdListIsNotEmpty extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Id list is not empty');
    }
}

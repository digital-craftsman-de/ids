<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/** @psalm-immutable */
final class IdListsMustNotBeEqual extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Id lists must not be equal');
    }
}

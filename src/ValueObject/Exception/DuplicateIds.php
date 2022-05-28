<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/** @psalm-immutable */
final class DuplicateIds extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Duplicate ids found');
    }
}

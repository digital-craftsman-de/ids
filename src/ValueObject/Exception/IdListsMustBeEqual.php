<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
final class IdListsMustBeEqual extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Id lists must be equal');
    }
}

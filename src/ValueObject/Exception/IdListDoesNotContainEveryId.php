<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
final class IdListDoesNotContainEveryId extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The id list does not contain every id of list');
    }
}

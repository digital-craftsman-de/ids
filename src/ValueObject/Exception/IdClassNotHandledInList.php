<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
final class IdClassNotHandledInList extends \InvalidArgumentException
{
    public function __construct(string $idListClass, string $idClass)
    {
        parent::__construct(sprintf(
            'The id list %s does not handle id of class %s',
            $idListClass,
            $idClass,
        ));
    }
}

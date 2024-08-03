<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\ValueObject;

use DigitalCraftsman\Ids\ValueObject\IdList;

/**
 * @extends IdList<UserId>
 */
final readonly class UserIdList extends IdList
{
    #[\Override]
    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}

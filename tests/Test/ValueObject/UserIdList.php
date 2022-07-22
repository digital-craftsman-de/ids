<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\ValueObject;

use DigitalCraftsman\Ids\ValueObject\IdList;

/** @extends IdList<UserId> */
final class UserIdList extends IdList
{
    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}

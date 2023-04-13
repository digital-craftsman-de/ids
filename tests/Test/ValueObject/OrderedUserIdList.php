<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\ValueObject;

use DigitalCraftsman\Ids\ValueObject\OrderedIdList;

/** @extends OrderedIdList<UserId> */
final class OrderedUserIdList extends OrderedIdList
{
    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}

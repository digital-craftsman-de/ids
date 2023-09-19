<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\Doctrine;

use DigitalCraftsman\Ids\Doctrine\IdListType;
use DigitalCraftsman\Ids\Test\ValueObject\OrderedUserIdList;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;

final class OrderedUserIdListType extends IdListType
{
    public static function getTypeName(): string
    {
        return 'ordered_user_id_list';
    }

    public static function getClass(): string
    {
        return OrderedUserIdList::class;
    }

    public static function getIdClass(): string
    {
        return UserId::class;
    }
}

<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\Doctrine;

use DigitalCraftsman\Ids\Doctrine\IdListType;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;

final class UserIdListType extends IdListType
{
    public static function getTypeName(): string
    {
        return 'user_id_list';
    }

    public static function getClass(): string
    {
        return UserIdList::class;
    }

    public static function getIdClass(): string
    {
        return UserId::class;
    }
}

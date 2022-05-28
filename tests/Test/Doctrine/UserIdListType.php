<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\Doctrine;

use DigitalCraftsman\Ids\Doctrine\IdListType;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;

final class UserIdListType extends IdListType
{
    protected function getTypeName(): string
    {
        return 'user_id_list';
    }

    protected function getIdListClass(): string
    {
        return UserIdList::class;
    }

    protected function getIdClass(): string
    {
        return UserId::class;
    }
}

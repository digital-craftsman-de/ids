<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\Doctrine;

use DigitalCraftsman\Ids\Doctrine\IdListType;
use DigitalCraftsman\Ids\Test\ValueObject\OrderedUserIdList;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;

final class OrderedUserIdListType extends IdListType
{
    protected function getTypeName(): string
    {
        return 'ordered_user_id_list';
    }

    protected function getIdListClass(): string
    {
        return OrderedUserIdList::class;
    }

    protected function getIdClass(): string
    {
        return UserId::class;
    }
}

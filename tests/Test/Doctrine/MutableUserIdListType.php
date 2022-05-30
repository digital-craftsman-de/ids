<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\Doctrine;

use DigitalCraftsman\Ids\Doctrine\MutableIdListType;
use DigitalCraftsman\Ids\Test\ValueObject\MutableUserIdList;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;

final class MutableUserIdListType extends MutableIdListType
{
    protected function getTypeName(): string
    {
        return 'mutable_user_id_list';
    }

    protected function getIdListClass(): string
    {
        return MutableUserIdList::class;
    }

    protected function getIdClass(): string
    {
        return UserId::class;
    }
}

<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\Doctrine;

use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use DigitalCraftsman\SelfAwareNormalizers\Doctrine\ArrayNormalizableType;

final class UserIdListType extends ArrayNormalizableType
{
    #[\Override]
    public static function getTypeName(): string
    {
        return 'user_id_list';
    }

    #[\Override]
    public static function getClass(): string
    {
        return UserIdList::class;
    }
}

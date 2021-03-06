<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\Doctrine;

use DigitalCraftsman\Ids\Doctrine\IdType;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;

final class UserIdType extends IdType
{
    protected function getTypeName(): string
    {
        return 'user_id';
    }

    protected function getIdClass(): string
    {
        return UserId::class;
    }
}

<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\ValueObject;

use DigitalCraftsman\Ids\ValueObject\MutableIdList;

/** @psalm-immutable */
final class MutableUserIdList extends MutableIdList
{
    /**
     * @var array<int, UserId>
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    public array $ids;

    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}

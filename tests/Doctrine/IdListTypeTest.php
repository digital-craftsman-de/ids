<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\Test\Doctrine\UserIdListType;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use PHPUnit\Framework\TestCase;

final class IdListTypeTest extends TestCase
{
    /** @test */
    public function convert_from_and_to_id_list_php_value_works(): void
    {
        // -- Arrange
        $userIdList = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);
        $userIdListType = new UserIdListType();
        $platform = new PostgreSQL100Platform();

        // -- Act
        $databaseValue = $userIdListType->convertToDatabaseValue($userIdList, $platform);
        $phpValue = $userIdListType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertEquals($userIdList, $phpValue);
    }
}

<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\Test\Doctrine\OrderedUserIdListType;
use DigitalCraftsman\Ids\Test\Doctrine\UserIdListType;
use DigitalCraftsman\Ids\Test\ValueObject\OrderedUserIdList;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
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
        $platform = new PostgreSQLPlatform();

        // -- Act
        $databaseValue = $userIdListType->convertToDatabaseValue($userIdList, $platform);
        $phpValue = $userIdListType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertEquals($userIdList, $phpValue);
    }

    /** @test */
    public function convert_from_and_to_ordered_id_list_php_value_works(): void
    {
        // -- Arrange
        $userIdList = new OrderedUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);
        $userIdListType = new OrderedUserIdListType();
        $platform = new PostgreSQLPlatform();

        // -- Act
        $databaseValue = $userIdListType->convertToDatabaseValue($userIdList, $platform);
        $phpValue = $userIdListType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertEquals($userIdList, $phpValue);
    }

    /** @test */
    public function convert_from_and_to_value_value_works(): void
    {
        // -- Arrange
        $userIdListType = new UserIdListType();
        $platform = new PostgreSQLPlatform();

        // -- Act
        $databaseValue = $userIdListType->convertToDatabaseValue(null, $platform);
        $phpValue = $userIdListType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertNull($phpValue);
    }
}

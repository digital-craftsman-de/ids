<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\Test\Doctrine\MutableUserIdListType;
use DigitalCraftsman\Ids\Test\ValueObject\MutableUserIdList;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\TestCase;

final class MutableIdListTypeTest extends TestCase
{
    /** @test */
    public function convert_from_and_to_id_list_php_value_works(): void
    {
        // -- Arrange
        $userIdList = new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);
        $userIdListType = new MutableUserIdListType();
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
        $userIdListType = new MutableUserIdListType();
        $platform = new PostgreSQLPlatform();

        // -- Act
        $databaseValue = $userIdListType->convertToDatabaseValue(null, $platform);
        $phpValue = $userIdListType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertNull($phpValue);
    }
}

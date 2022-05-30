<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\Test\Doctrine\UserIdType;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\TestCase;

final class IdTypeTest extends TestCase
{
    /** @test */
    public function convert_from_and_to_id_php_value_works(): void
    {
        // -- Arrange
        $userId = UserId::generateRandom();
        $userIdType = new UserIdType();
        $platform = new PostgreSQLPlatform();

        // -- Act
        $databaseValue = $userIdType->convertToDatabaseValue($userId, $platform);
        $phpValue = $userIdType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertEquals($userId, $phpValue);
    }

    /** @test */
    public function convert_from_and_to_null_value_works(): void
    {
        // -- Arrange
        $userIdType = new UserIdType();
        $platform = new PostgreSQLPlatform();

        // -- Act
        $databaseValue = $userIdType->convertToDatabaseValue(null, $platform);
        $phpValue = $userIdType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertNull($phpValue);
    }
}

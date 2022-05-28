<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\Test\Doctrine\UserIdType;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use PHPUnit\Framework\TestCase;

final class BaseIdTypeTest extends TestCase
{
    /** @test */
    public function convert_from_and_to_id_php_value_works(): void
    {
        // -- Arrange
        $userId = UserId::generateRandom();
        $userIdType = new UserIdType();
        $platform = new PostgreSQL100Platform();

        // -- Act
        $databaseValue = $userIdType->convertToDatabaseValue($userId, $platform);
        $phpValue = $userIdType->convertToPHPValue($databaseValue, $platform);

        // -- Assert
        self::assertEquals($userId, $phpValue);
    }
}

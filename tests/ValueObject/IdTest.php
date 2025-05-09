<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\Exception\NotTheSameUser;
use DigitalCraftsman\Ids\Test\Exception\SameUser;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
final class IdTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function construction_works(): void
    {
        // -- Act
        new UserId('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
        UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
        UserId::generateRandom();
    }

    #[Test]
    public function construction_with_invalid_id_fails(): void
    {
        // -- Assert
        $this->expectException(Exception\InvalidId::class);

        // -- Act
        new UserId('test');
    }

    #[Test]
    public function to_string_works(): void
    {
        // -- Arrange
        $idString = 'f41e0af4-88c4-4d79-9c1a-6e8ea34a956f';
        $id = UserId::fromString($idString);

        // -- Act & Assert
        self::assertSame($idString, $id->toString());
    }

    // -- String normalizable

    #[Test]
    public function normalize_and_denormalize_works(): void
    {
        // -- Arrange
        $id = UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
        $data = 'f41e0af4-88c4-4d79-9c1a-6e8ea34a956f';

        // -- Act
        $normalized = $id->normalize();
        $denormalized = UserId::denormalize($data);

        // -- Assert
        self::assertSame($data, $normalized);
        self::assertEquals($id, $denormalized);
    }

    #[Test]
    public function user_id_is_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
        $userId2 = UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');

        // -- Act & Assert
        self::assertTrue($userId1->isEqualTo($userId2));
    }

    #[Test]
    public function user_id_is_not_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        // -- Act & Assert
        self::assertTrue($userId1->isNotEqualTo($userId2));
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function user_id_must_be_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::fromString((string) $userId1);

        // -- Act
        $userId1->mustBeEqualTo($userId2);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function user_id_must_not_be_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        // -- Act
        $userId1->mustNotBeEqualTo($userId2);
    }

    #[Test]
    public function user_id_must_be_equal_fails(): void
    {
        // -- Assert
        $this->expectException(Exception\IdNotEqual::class);

        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        // -- Act
        $userId1->mustBeEqualTo($userId2);
    }

    #[Test]
    public function user_id_must_be_equal_fails_with_custom_exception(): void
    {
        // -- Assert
        $this->expectException(NotTheSameUser::class);

        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        // -- Act
        $userId1->mustBeEqualTo(
            $userId2,
            otherwiseThrow: static fn () => new NotTheSameUser(),
        );
    }

    #[Test]
    public function user_id_must_not_be_equal_fails(): void
    {
        // -- Assert
        $this->expectException(Exception\IdEqual::class);

        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = new UserId((string) $userId1);

        // -- Act
        $userId1->mustNotBeEqualTo($userId2);
    }

    #[Test]
    public function user_id_must_not_be_equal_fails_with_custom_exception(): void
    {
        // -- Assert
        $this->expectException(SameUser::class);

        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = new UserId((string) $userId1);

        // -- Act
        $userId1->mustNotBeEqualTo(
            $userId2,
            otherwiseThrow: static fn () => new SameUser(),
        );
    }
}

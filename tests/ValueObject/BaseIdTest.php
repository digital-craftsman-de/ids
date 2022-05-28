<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\IdNotEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\InvalidId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/** @coversDefaultClass \DigitalCraftsman\Ids\ValueObject\BaseId */
final class BaseIdTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::fromString
     * @doesNotPerformAssertions
     */
    public function construction_works(): void
    {
        new UserId('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
        UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function construction_with_invalid_id_fails(): void
    {
        $this->expectException(InvalidId::class);

        new UserId('test');
    }

    /**
     * @test
     * @covers ::isNotEqualTo
     */
    public function user_id_is_not_equal(): void
    {
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        self::assertTrue($userId1->isNotEqualTo($userId2));
    }

    /**
     * @test
     * @covers ::isExistingInList
     */
    public function user_id_is_existing_in_list(): void
    {
        $uuid = Uuid::uuid4()->toString();

        $userIdToSearch = new UserId($uuid);

        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        $listOfUserIdsIncludingSameInstance = [
            $userIdToSearch,
            $userId1,
            $userId2,
        ];

        self::assertTrue($userIdToSearch->isExistingInList($listOfUserIdsIncludingSameInstance));

        $copyOfStringValue = new UserId((string) $userIdToSearch);

        $listOfUserIdsIncludingEqualInstance = [
            $copyOfStringValue,
            $userId1,
            $userId2,
        ];

        self::assertTrue($userIdToSearch->isExistingInList($listOfUserIdsIncludingEqualInstance));
    }

    /**
     * @test
     * @covers ::isExistingInList
     */
    public function user_id_is_not_existing_in_list(): void
    {
        $uuid = Uuid::uuid4()->toString();

        $userIdToSearch = new UserId($uuid);

        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        $listOfUserIdsWithoutIdToSearch = [
            $userId1,
            $userId2,
        ];

        self::assertFalse($userIdToSearch->isExistingInList($listOfUserIdsWithoutIdToSearch));
    }

    /**
     * @test
     * @covers ::mustBeEqualTo
     * @doesNotPerformAssertions
     */
    public function user_id_must_be_equal(): void
    {
        $userId1 = UserId::generateRandom();
        $userId2 = new UserId((string) $userId1);

        $userId1->mustBeEqualTo($userId2);
    }

    /**
     * @test
     * @covers ::mustNotBeEqualTo
     * @doesNotPerformAssertions
     */
    public function user_id_must_not_be_equal(): void
    {
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        $userId1->mustNotBeEqualTo($userId2);
    }

    /**
     * @test
     * @covers ::mustBeEqualTo
     */
    public function user_id_must_be_equal_fails(): void
    {
        $this->expectException(IdNotEqual::class);

        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        $userId1->mustBeEqualTo($userId2);
    }

    /**
     * @test
     * @covers ::mustNotBeEqualTo
     */
    public function user_id_must_not_be_same_fails(): void
    {
        $this->expectException(IdEqual::class);

        $userId1 = UserId::generateRandom();
        $userId2 = new UserId((string) $userId1);

        $userId1->mustNotBeEqualTo($userId2);
    }
}

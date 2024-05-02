<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\ValueObject\AdminId;
use DigitalCraftsman\Ids\Test\ValueObject\InstructorId;
use DigitalCraftsman\Ids\Test\ValueObject\ProjectId;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\Ids\ValueObject\IdList */
final class IdListTest extends TestCase
{
    // -- Construct

    /**
     * @test
     *
     * @covers ::__construct
     */
    public function id_list_construction_works(): void
    {
        // -- Arrange & Act
        $idList = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Assert
        self::assertCount(3, $idList->ids);
    }

    /**
     * @test
     *
     * @covers ::__construct
     */
    public function id_list_construction_works_with_index_that_is_not_a_list(): void
    {
        // -- Arrange & Act
        $idList = new UserIdList([
            0 => UserId::generateRandom(),
            2 => UserId::generateRandom(),
            33 => UserId::generateRandom(),
        ]);

        // -- Assert
        self::assertCount(3, $idList->ids);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::mustOnlyContainIdsOfHandledClass
     *
     * @doesNotPerformAssertions
     */
    public function id_list_construction_works_with_ids_of_subclass(): void
    {
        // -- Arrange & Act
        new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            InstructorId::generateRandom(),
            AdminId::generateRandom(),
        ]);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::mustOnlyContainIdsOfHandledClass
     */
    public function id_list_construction_fails_with_ids_of_different_id_class(): void
    {
        // -- Assert
        $this->expectException(Exception\IdClassNotHandledInList::class);

        // -- Arrange & Act
        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore-next-line
         */
        new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
            ProjectId::generateRandom(),
        ]);
    }

    /**
     * @test
     *
     * @covers ::fromIds
     */
    public function id_list_construction_from_ids_works(): void
    {
        // -- Arrange
        $ids = [
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ];

        // -- Act
        $userIdList = UserIdList::fromIds($ids);

        // -- Assert
        self::assertCount(3, $userIdList);
    }

    /**
     * @test
     *
     * @covers ::fromIdStrings
     */
    public function id_list_construction_from_id_strings_works(): void
    {
        // -- Arrange
        $idStrings = [
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
        ];

        // -- Act
        $userIdList = UserIdList::fromIdStrings($idStrings);

        // -- Assert
        self::assertCount(3, $userIdList);
    }

    /**
     * @test
     *
     * @covers ::fromMap
     */
    public function id_list_construction_from_map_works(): void
    {
        // -- Arrange
        $users = [
            [
                'id' => (string) UserId::generateRandom(),
                'name' => 'Tom',
            ],
            [
                'id' => (string) UserId::generateRandom(),
                'name' => 'Ralf',
            ],
            [
                'id' => (string) UserId::generateRandom(),
                'name' => 'Marc',
            ],
        ];

        // -- Act
        $userIdList = UserIdList::fromMap(
            $users,
            static fn (array $user): UserId => UserId::fromString($user['id']),
        );

        // -- Assert
        self::assertCount(3, $userIdList);
        self::assertSame(UserIdList::class, $userIdList::class);
        self::assertTrue($userIdList->containsId(UserId::fromString($users[0]['id'])));
        self::assertTrue($userIdList->containsId(UserId::fromString($users[1]['id'])));
        self::assertTrue($userIdList->containsId(UserId::fromString($users[2]['id'])));
    }

    /**
     * @test
     *
     * @covers ::emptyList
     */
    public function empty_list_works(): void
    {
        // -- Arrange
        $emptyIdList = UserIdList::emptyList();

        // -- Act & Assert
        self::assertCount(0, $emptyIdList);
    }

    // -- Merge

    /**
     * @test
     *
     * @covers ::fromIdLists
     */
    public function from_id_lists_works(): void
    {
        // -- Arrange
        $idList1 = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idList2 = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $mergedIdList = UserIdList::fromIdLists([
            $idList1,
            $idList2,
        ]);

        // -- Assert
        self::assertCount(6, $mergedIdList);
    }

    /**
     * @test
     *
     * @covers ::fromIdLists
     */
    public function from_id_lists_with_duplicates_works(): void
    {
        // -- Arrange
        $idList1 = new UserIdList([
            new UserId('41918847-b781-4046-94ce-2fddf5674d9e'),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idList2 = new UserIdList([
            new UserId('41918847-b781-4046-94ce-2fddf5674d9e'),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $mergedIdList = UserIdList::fromIdLists([
            $idList1,
            $idList2,
        ]);

        // -- Assert
        self::assertCount(5, $mergedIdList);
    }

    // -- Add id

    /**
     * @test
     *
     * @covers ::addId
     */
    public function add_id_works(): void
    {
        // -- Arrange
        $idList = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $newId = UserId::generateRandom();

        // -- Act
        $addedList = $idList->addId($newId);

        // -- Assert
        self::assertCount(2, $idList);
        self::assertCount(3, $addedList);

        self::assertTrue($addedList->containsId($newId));
    }

    /**
     * @test
     *
     * @covers ::addId
     */
    public function add_id_fails_with_duplicate_id(): void
    {
        // -- Assert
        $this->expectException(Exception\IdAlreadyInList::class);

        // -- Arrange
        $existingUserId = UserId::generateRandom();
        $idList = new UserIdList([
            $existingUserId,
            UserId::generateRandom(),
        ]);

        // -- Act
        $idList->addId($existingUserId);
    }

    /**
     * @test
     *
     * @covers ::addIdWhenNotInList
     */
    public function add_id_when_not_in_list_works(): void
    {
        // -- Arrange
        $existingId = UserId::generateRandom();
        $idList = new UserIdList([
            $existingId,
            UserId::generateRandom(),
        ]);

        $newId = UserId::generateRandom();

        // -- Act
        $notAddedList = $idList->addIdWhenNotInList($existingId);
        $addedList = $idList->addIdWhenNotInList($newId);

        // -- Assert
        self::assertCount(2, $idList);
        self::assertCount(2, $notAddedList);
        self::assertCount(3, $addedList);

        self::assertTrue($notAddedList->containsId($existingId));
        self::assertTrue($addedList->containsId($existingId));
        self::assertTrue($addedList->containsId($newId));
    }

    // -- Add ids

    /**
     * @test
     *
     * @covers ::addIds
     */
    public function add_ids_works(): void
    {
        // -- Arrange
        $idList = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $newIds = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $addedList = $idList->addIds($newIds);

        // -- Assert
        self::assertCount(2, $idList);
        self::assertCount(4, $addedList);

        self::assertTrue($addedList->containsEveryId($newIds));
    }

    /**
     * @test
     *
     * @covers ::addIds
     */
    public function add_ids_fails_with_duplicate_id(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesContainNoneIds::class);

        // -- Arrange
        $existingUserId = UserId::generateRandom();

        $idList = new UserIdList([
            $existingUserId,
            UserId::generateRandom(),
        ]);

        $newIds = new UserIdList([
            $existingUserId,
            UserId::generateRandom(),
        ]);

        // -- Act
        $idList->addIds($newIds);
    }

    /**
     * @test
     *
     * @covers ::addIdsWhenNotInList
     */
    public function add_ids_when_not_in_list_works(): void
    {
        // -- Arrange
        $existingId = UserId::generateRandom();
        $idList = new UserIdList([
            $existingId,
            UserId::generateRandom(),
        ]);

        $newId = UserId::generateRandom();

        $newIds = new UserIdList([
            $existingId,
            $newId,
        ]);

        // -- Act
        $addedList = $idList->addIdsWhenNotInList($newIds);

        // -- Assert
        self::assertCount(2, $idList);
        self::assertCount(3, $addedList);

        self::assertTrue($addedList->containsId($existingId));
        self::assertTrue($addedList->containsId($newId));
    }

    // -- Remove id

    /**
     * @test
     *
     * @covers ::removeId
     */
    public function remove_id_works(): void
    {
        // -- Arrange
        $idToRemove = UserId::generateRandom();

        $idList = new UserIdList([
            $idToRemove,
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $removedList = $idList->removeId($idToRemove);

        // -- Assert
        self::assertCount(3, $idList);
        self::assertCount(2, $removedList);

        self::assertTrue($removedList->notContainsId($idToRemove));
    }

    /**
     * @test
     *
     * @covers ::removeId
     */
    public function remove_id_fails_when_id_is_not_in_list(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesNotContainId::class);

        // -- Arrange
        $idToRemove = UserId::generateRandom();

        $idList = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $idList->removeId($idToRemove);
    }

    // Remove ids

    /**
     * @test
     *
     * @covers ::removeIds
     */
    public function remove_ids_works(): void
    {
        // -- Arrange
        $idToRemove1 = UserId::generateRandom();
        $idToRemove2 = UserId::generateRandom();

        $idList = new UserIdList([
            UserId::fromString((string) $idToRemove1),
            UserId::fromString((string) $idToRemove2),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idsToRemove = new UserIdList([
            $idToRemove1,
            $idToRemove2,
        ]);

        // -- Act
        $removedList = $idList->removeIds($idsToRemove);

        // -- Assert
        self::assertCount(4, $idList);
        self::assertCount(2, $removedList);

        self::assertTrue($removedList->notContainsId($idToRemove1));
        self::assertTrue($removedList->notContainsId($idToRemove2));
    }

    /**
     * @test
     *
     * @covers ::removeIds
     */
    public function remove_ids_fails_when_id_is_not_in_list(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesNotContainEveryId::class);

        // -- Arrange
        $idToRemove1 = UserId::generateRandom();
        $idToRemove2 = UserId::generateRandom();

        $idList = new UserIdList([
            $idToRemove1,
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idsToRemove = new UserIdList([
            $idToRemove1,
            $idToRemove2,
        ]);

        // -- Act
        $idList->removeIds($idsToRemove);
    }

    /**
     * @test
     *
     * @covers ::removeIdsWhenInList
     */
    public function remove_ids_when_in_list_works(): void
    {
        // -- Arrange
        $idToRemove1 = UserId::generateRandom();
        $idToRemove2 = UserId::generateRandom();
        $idToRemove3 = UserId::generateRandom();

        $idList = new UserIdList([
            UserId::fromString((string) $idToRemove1),
            UserId::fromString((string) $idToRemove2),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idsToRemove = new UserIdList([
            $idToRemove1,
            $idToRemove2,
            $idToRemove3,
        ]);

        // -- Act
        $removedList = $idList->removeIdsWhenInList($idsToRemove);

        // -- Assert
        self::assertCount(4, $idList);
        self::assertCount(2, $removedList);

        self::assertTrue($removedList->notContainsId($idToRemove1));
        self::assertTrue($removedList->notContainsId($idToRemove2));
    }

    /**
     * @test
     *
     * @covers ::removeIdWhenInList
     */
    public function remove_id_when_in_list_works(): void
    {
        // -- Arrange
        $idToRemove = UserId::generateRandom();

        $idList = new UserIdList([
            $idToRemove,
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idNotInList = UserId::generateRandom();

        // -- Act
        $removedList = $idList->removeIdWhenInList($idToRemove);
        $removedList = $removedList->removeIdWhenInList($idNotInList);

        // -- Assert
        self::assertCount(3, $idList);
        self::assertCount(2, $removedList);

        self::assertTrue($removedList->notContainsId($idToRemove));
    }

    // -- Diff

    /**
     * @test
     *
     * @covers ::diff
     */
    public function id_list_diff_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $diffListFromOriginalList = $originalList->diff($partialList);
        $diffListFromPartialList = $partialList->diff($originalList);

        // -- Assert
        self::assertCount(2, $diffListFromOriginalList);
        self::assertCount(0, $diffListFromPartialList);

        self::assertTrue($diffListFromOriginalList->containsId($idMarkus));
        self::assertTrue($diffListFromOriginalList->containsId($idTom));
    }

    /**
     * @test
     *
     * @covers ::diff
     */
    public function id_list_diff_works_with_empty_list(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $emptyList = UserIdList::emptyList();

        // -- Act
        $diffListFromOriginal = $originalList->diff($emptyList);
        $diffListFromEmpty = $emptyList->diff($originalList);

        // -- Assert
        self::assertCount(4, $diffListFromOriginal);
        self::assertCount(0, $diffListFromEmpty);
    }

    // -- Intersect

    /**
     * @test
     *
     * @covers ::intersect
     */
    public function id_list_intersect_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $fullList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $intersectedList = $fullList->intersect($partialList);

        // -- Assert
        self::assertCount(2, $intersectedList);

        self::assertTrue($intersectedList->containsId($idAnton));
        self::assertTrue($intersectedList->containsId($idPaul));
    }

    // -- Must and must not contain

    /**
     * @test
     *
     * @covers ::mustContainId
     * @covers ::mustNotContainId
     *
     * @doesNotPerformAssertions
     */
    public function id_list_must_and_must_not_contains_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $listWithAllIds = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act & Assert
        $listWithAllIds->mustContainId($idMarkus);
        $partialList->mustNotContainId($idMarkus);
    }

    /**
     * @test
     *
     * @covers ::mustContainId
     */
    public function id_list_must_contain_throws_exception(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesNotContainId::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $partialList = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $partialList->mustContainId($idMarkus);
    }

    /**
     * @test
     *
     * @covers ::mustNotContainId
     */
    public function id_list_must_not_contain_throws_exception(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesContainId::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $partialList = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $partialList->mustNotContainId($idAnton);
    }

    /**
     * @test
     *
     * @covers ::mustContainEveryId
     */
    public function id_list_must_contain_every_id(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesNotContainEveryId::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $fullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
        ]);

        // -- Act
        $partialList->mustContainEveryId($fullList);
    }

    /**
     * @test
     *
     * @covers ::mustContainEveryId
     *
     * @doesNotPerformAssertions
     */
    public function id_list_must_contain_every_id_when_every_id_is_present(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $fullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
        ]);

        // -- Act & Assert
        $fullList->mustContainEveryId($partialList);
    }

    /**
     * @test
     *
     * @covers ::mustNotContainEveryId
     */
    public function id_list_must_not_contain_every_id(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesContainEveryId::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $fullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
        ]);

        // -- Act
        $fullList->mustNotContainEveryId($partialList);
    }

    /**
     * @test
     *
     * @covers ::mustNotContainEveryId
     *
     * @doesNotPerformAssertions
     */
    public function id_list_must_not_contain_every_id_when_every_id_is_present(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $fullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
        ]);

        // -- Act & Assert
        $partialList->mustNotContainEveryId($fullList);
    }

    /**
     * @test
     *
     * @covers ::mustContainSomeIds
     */
    public function id_list_must_contain_some_ids(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesNotContainSomeIds::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        $almostFullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $idListWithDifferentId = UserIdList::fromIds([
            clone $idPeter,
        ]);

        // -- Act
        $almostFullList->mustContainSomeIds($idListWithDifferentId);
    }

    /**
     * @test
     *
     * @covers ::mustContainSomeIds
     *
     * @doesNotPerformAssertions
     */
    public function id_list_must_contain_some_ids_when_one_id_is_available(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        $almostFullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $idListWithDifferentId = UserIdList::fromIds([
            clone $idPaul,
            clone $idPeter,
        ]);

        // -- Act & Assert
        $almostFullList->mustContainSomeIds($idListWithDifferentId);
    }

    /**
     * @test
     *
     * @covers ::mustContainNoneIds
     */
    public function id_list_must_contain_none_ids(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListDoesContainNoneIds::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        $almostFullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $idListWithPartialMatchingIds = UserIdList::fromIds([
            clone $idPeter,
            clone $idPaul,
        ]);

        // -- Act
        $idListWithPartialMatchingIds->mustContainNoneIds($almostFullList);
    }

    /**
     * @test
     *
     * @covers ::mustContainNoneIds
     *
     * @doesNotPerformAssertions
     */
    public function id_list_must_contain_none_ids_when_no_id_is_available(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        $almostFullList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $idListWithDifferentId = UserIdList::fromIds([
            clone $idPeter,
        ]);

        // -- Act & Assert
        $almostFullList->mustContainNoneIds($idListWithDifferentId);
    }

    // -- Must be empty

    /**
     * @test
     *
     * @covers ::mustBeEmpty
     *
     * @doesNotPerformAssertions
     */
    public function id_list_must_be_empty_works(): void
    {
        // -- Arrange
        $emptyList = UserIdList::emptyList();

        // -- Act
        $emptyList->mustBeEmpty();
    }

    /**
     * @test
     *
     * @covers ::mustBeEmpty
     */
    public function id_list_must_be_empty_throws_exception_when_not_empty(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListIsNotEmpty::class);

        // -- Arrange
        $notEmptyList = new UserIdList([
            UserId::generateRandom(),
        ]);

        // -- Act
        $notEmptyList->mustBeEmpty();
    }

    // -- Must not be empty

    /**
     * @test
     *
     * @covers ::mustNotBeEmpty
     *
     * @doesNotPerformAssertions
     */
    public function id_list_must_not_be_empty_works(): void
    {
        // -- Arrange
        $notEmptyList = new UserIdList([
            UserId::generateRandom(),
        ]);

        // -- Act
        $notEmptyList->mustNotBeEmpty();
    }

    /**
     * @test
     *
     * @covers ::mustNotBeEmpty
     */
    public function id_list_must_not_be_empty_throws_exception_when_empty(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListIsEmpty::class);

        // -- Arrange
        $emptyList = UserIdList::emptyList();

        // -- Act
        $emptyList->mustNotBeEmpty();
    }

    // -- Empty

    /**
     * @test
     *
     * @covers ::isEmpty
     * @covers ::isNotEmpty
     */
    public function id_list_is_empty_works(): void
    {
        // -- Arrange
        $emptyList = UserIdList::emptyList();
        $notEmptyList = new UserIdList([
            UserId::generateRandom(),
        ]);

        // -- Act & Assert
        self::assertTrue($emptyList->isEmpty());
        self::assertFalse($notEmptyList->isEmpty());

        self::assertTrue($notEmptyList->isNotEmpty());
        self::assertFalse($emptyList->isNotEmpty());
    }

    // -- Map

    /**
     * @test
     *
     * @covers ::map
     */
    public function id_list_map_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $listWithAllIds = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $expectedArray = [
            (string) $idAnton,
            (string) $idMarkus,
            (string) $idPaul,
            (string) $idTom,
        ];

        // -- Act
        /** @var array<int, string> $stringArray */
        $stringArray = $listWithAllIds->map(
            static fn (UserId $userId) => (string) $userId,
        );

        // -- Assert
        self::assertSame($expectedArray, $stringArray);
    }

    // -- Filter

    /**
     * @test
     *
     * @covers ::filter
     */
    public function id_list_filter_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $listWithAllIds = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $externalIdsToMatch = UserIdList::fromIds([
            $idAnton,
            $idTom,
        ]);

        $expectedArray = [
            (string) $idMarkus,
            (string) $idPaul,
        ];

        // -- Act
        $filteredList = $listWithAllIds->filter(
            static fn (UserId $userId) => $externalIdsToMatch->notContainsId($userId),
        );

        // -- Assert
        self::assertSame($expectedArray, $filteredList->idsAsStringList());
    }

    // -- Every

    /**
     * @test
     *
     * @covers ::every
     */
    public function id_list_every_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idChris = UserId::generateRandom();

        $listWithAllIdsOfGroupSparta = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $listWithIdsThatAreAllInGroupSparta = UserIdList::fromIds([
            $idAnton,
            $idTom,
        ]);

        $listWithIdsThatAreNotAllInGroupSparta = UserIdList::fromIds([
            $idAnton,
            $idTom,
            $idChris,
        ]);

        // -- Act
        $everyIdIsIncluded = $listWithIdsThatAreAllInGroupSparta->every(
            static fn (UserId $userId) => $listWithAllIdsOfGroupSparta->containsId($userId),
        );
        $notEveryIdIsIncluded = $listWithIdsThatAreNotAllInGroupSparta->every(
            static fn (UserId $userId) => $listWithAllIdsOfGroupSparta->containsId($userId),
        );

        // -- Assert
        self::assertTrue($everyIdIsIncluded);
        self::assertFalse($notEveryIdIsIncluded);
    }

    // -- Some

    /**
     * @test
     *
     * @covers ::some
     */
    public function id_list_some_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idChris = UserId::generateRandom();
        $idQuirin = UserId::generateRandom();

        $listWithAllIdsOfGroupSparta = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $listWithIdsThatContainSomeOfSpartaGroup = UserIdList::fromIds([
            $idAnton,
            $idTom,
            $idChris,
        ]);

        $listWithIdsThatContainNoneOfSpartaGroup = UserIdList::fromIds([
            $idChris,
            $idQuirin,
        ]);

        // -- Act
        $someIdIsIncluded = $listWithIdsThatContainSomeOfSpartaGroup->some(
            static fn (UserId $userId) => $listWithAllIdsOfGroupSparta->containsId($userId),
        );
        $noneIdIsIncluded = $listWithIdsThatContainNoneOfSpartaGroup->some(
            static fn (UserId $userId) => $listWithAllIdsOfGroupSparta->containsId($userId),
        );

        // -- Assert
        self::assertTrue($someIdIsIncluded);
        self::assertFalse($noneIdIsIncluded);
    }

    // -- Reduce

    /**
     * @test
     *
     * @covers ::reduce
     */
    public function id_list_reduce_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $amountsPerUser = [
            (string) $idAnton => 20,
            (string) $idMarkus => 30,
            (string) $idPaul => 25,
            (string) $idTom => 17,
        ];

        $listWithIdsOfAllUsers = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        // -- Act
        $amountsOfAllUsers = $listWithIdsOfAllUsers->reduce(
            static fn (int $carry, UserId $userId) => $carry + $amountsPerUser[(string) $userId],
            0,
        );

        // -- Assert
        self::assertSame(92, $amountsOfAllUsers);
    }

    // -- Contains

    /**
     * @test
     *
     * @covers ::containsId
     * @covers ::notContainsId
     */
    public function id_list_contains_and_not_contains_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        // Generate new ids to make sure that it's enough for ids to be equal instead of same instance.
        $listWithAllIds = UserIdList::fromIds([
            UserId::fromString((string) $idAnton),
            UserId::fromString((string) $idMarkus),
            UserId::fromString((string) $idPaul),
            UserId::fromString((string) $idTom),
        ]);

        $partialList = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act & Assert
        self::assertTrue($listWithAllIds->containsId($idAnton));
        self::assertFalse($partialList->containsId($idMarkus));

        self::assertTrue($partialList->notContainsId($idMarkus));
        self::assertFalse($listWithAllIds->notContainsId($idMarkus));
    }

    // -- Contains every id

    /**
     * @test
     *
     * @covers ::containsEveryId
     */
    public function id_list_contains_every_id_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        // Generate new ids to make sure that it's enough for ids to be equal instead of same instance.
        $listWithAlmostAllIds = UserIdList::fromIds([
            clone $idAnton,
            clone $idMarkus,
            clone $idPaul,
            clone $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $listWithDifferentId = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
            clone $idPeter,
        ]);

        // -- Act & Assert
        self::assertTrue($listWithAlmostAllIds->containsEveryId($partialList));
        self::assertFalse($partialList->containsEveryId($listWithAlmostAllIds));

        self::assertFalse($listWithAlmostAllIds->containsEveryId($listWithDifferentId));
        self::assertFalse($partialList->containsEveryId($listWithDifferentId));
    }

    /**
     * @test
     *
     * @covers ::notContainsEveryId
     */
    public function id_list_not_contains_every_id_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        // Generate new ids to make sure that it's enough for ids to be equal instead of same instance.
        $listWithAlmostAllIds = UserIdList::fromIds([
            clone $idAnton,
            clone $idMarkus,
            clone $idPaul,
            clone $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $listWithDifferentId = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
            clone $idPeter,
        ]);

        // -- Act & Assert
        self::assertFalse($listWithAlmostAllIds->notContainsEveryId($partialList));
        self::assertTrue($partialList->notContainsEveryId($listWithAlmostAllIds));

        self::assertTrue($listWithAlmostAllIds->notContainsEveryId($listWithDifferentId));
        self::assertTrue($partialList->notContainsEveryId($listWithDifferentId));
    }

    // -- Contains some ids

    /**
     * @test
     *
     * @covers ::containsSomeIds
     */
    public function id_list_contains_some_ids_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        // Generate new ids to make sure that it's enough for ids to be equal instead of same instance.
        $listWithAlmostAllIds = UserIdList::fromIds([
            clone $idAnton,
            clone $idMarkus,
            clone $idPaul,
            clone $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $listWithDifferentId = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
            clone $idPeter,
        ]);

        $listWithOnlyDifferentIds = UserIdList::fromIds([
            clone $idPeter,
        ]);

        // -- Act & Assert
        self::assertTrue($listWithAlmostAllIds->containsSomeIds($partialList));
        self::assertTrue($partialList->containsSomeIds($listWithAlmostAllIds));

        self::assertTrue($listWithAlmostAllIds->containsSomeIds($listWithDifferentId));
        self::assertTrue($partialList->containsSomeIds($listWithDifferentId));

        self::assertFalse($listWithAlmostAllIds->containsSomeIds($listWithOnlyDifferentIds));
        self::assertFalse($partialList->containsSomeIds($listWithOnlyDifferentIds));
    }

    /**
     * @test
     *
     * @covers ::containsNoneIds
     */
    public function id_list_contains_none_ids_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idPeter = UserId::generateRandom();

        // Generate new ids to make sure that it's enough for ids to be equal instead of same instance.
        $listWithAlmostAllIds = UserIdList::fromIds([
            clone $idAnton,
            clone $idMarkus,
            clone $idPaul,
            clone $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $listWithDifferentId = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
            clone $idPeter,
        ]);

        $listWithOnlyDifferentIds = UserIdList::fromIds([
            clone $idPeter,
        ]);

        // -- Act & Assert
        self::assertFalse($listWithAlmostAllIds->containsNoneIds($partialList));
        self::assertFalse($partialList->containsNoneIds($listWithAlmostAllIds));

        self::assertFalse($listWithAlmostAllIds->containsNoneIds($listWithDifferentId));
        self::assertFalse($partialList->containsNoneIds($listWithDifferentId));

        self::assertTrue($listWithAlmostAllIds->containsNoneIds($listWithOnlyDifferentIds));
        self::assertTrue($partialList->containsNoneIds($listWithOnlyDifferentIds));
    }

    // -- Is equal and not equal

    /**
     * @test
     *
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     */
    public function id_list_is_equal_to(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idMarc = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            clone $idAnton,
            clone $idMarkus,
            clone $idPaul,
            clone $idTom,
        ]);

        $copyOfOriginalList = UserIdList::fromIds([
            clone $idAnton,
            clone $idMarkus,
            clone $idPaul,
            clone $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            clone $idAnton,
            clone $idPaul,
        ]);

        $listWithOneExchanged = UserIdList::fromIds([
            clone $idAnton,
            clone $idMarkus,
            clone $idPaul,
            clone $idMarc,
        ]);

        // -- Act & Assert
        self::assertTrue($originalList->isEqualTo($copyOfOriginalList));
        self::assertFalse($originalList->isEqualTo($partialList));
        self::assertFalse($originalList->isEqualTo($listWithOneExchanged));

        self::assertTrue($originalList->isNotEqualTo($partialList));
        self::assertFalse($originalList->isNotEqualTo($copyOfOriginalList));
    }

    /**
     * @test
     *
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     */
    public function empty_id_list_is_not_equal_to(): void
    {
        // -- Arrange
        $idTom = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();

        $emptyIdList = UserIdList::fromIds([]);
        $userIdList = UserIdList::fromIds([
            $idTom,
            $idMarkus,
        ]);

        // -- Act & Assert
        $this->assertFalse($emptyIdList->isEqualTo($userIdList));

        $this->assertTrue($emptyIdList->isNotEqualTo($userIdList));
    }

    /**
     * @test
     *
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     */
    public function id_list_is_not_equal_to_empty_id_list(): void
    {
        // -- Arrange
        $idTom = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();

        $emptyIdList = UserIdList::fromIds([]);
        $userIdList = UserIdList::fromIds([
            $idTom,
            $idMarkus,
        ]);

        // -- Act & Assert
        $this->assertFalse($userIdList->isEqualTo($emptyIdList));

        $this->assertTrue($userIdList->isNotEqualTo($emptyIdList));
    }

    /**
     * @test
     *
     * @covers ::mustBeEqualTo
     */
    public function must_be_equal_to(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListsMustBeEqual::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $originalList->mustBeEqualTo($partialList);
    }

    /**
     * @test
     *
     * @covers ::mustNotBeEqualTo
     */
    public function must_not_be_equal_to(): void
    {
        // -- Assert
        $this->expectException(Exception\IdListsMustNotBeEqual::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $sameListInDifferentOrder = UserIdList::fromIds([
            $idPaul,
            $idAnton,
            $idMarkus,
            $idTom,
        ]);

        // -- Act
        $originalList->mustNotBeEqualTo($sameListInDifferentOrder);
    }

    // -- Count

    /**
     * @test
     *
     * @covers ::count
     */
    public function id_list_count_works(): void
    {
        // -- Arrange
        $idList = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act & Assert
        self::assertSame(3, $idList->count());
    }

    // -- Iteration

    /**
     * @test
     *
     * @covers ::getIterator
     */
    public function id_list_iteration_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idList = new UserIdList([
            $idAnton,
            $idMarkus,
            $idPaul,
        ]);

        $duplicatedIdList = new UserIdList([
            $idAnton,
            $idMarkus,
            $idPaul,
        ]);

        $expectedString = sprintf(
            '%d%s%d%s%d%s',
            0,
            (string) $idAnton,
            1,
            (string) $idMarkus,
            2,
            (string) $idPaul,
        );

        // -- Act
        $concatenatedIds = '';
        foreach ($idList as $key => $id) {
            $concatenatedIds .= (string) $key;
            $concatenatedIds .= (string) $id;
        }

        // -- Assert
        self::assertSame($expectedString, $concatenatedIds);

        // Id list is equal after iteration
        self::assertEquals($duplicatedIdList, $idList);
    }

    /**
     * @test
     *
     * @covers ::getIterator
     */
    public function id_list_works_with_gaps_in_input_list(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idList = new UserIdList([
            0 => $idAnton,
            1 => $idMarkus,
            3 => $idPaul,
        ]);

        $expectedString = sprintf(
            '%s%s%s',
            (string) $idAnton,
            (string) $idMarkus,
            (string) $idPaul,
        );

        // -- Act
        $concatenatedIds = '';
        foreach ($idList as $id) {
            $concatenatedIds .= (string) $id;
        }

        // -- Assert
        self::assertSame($expectedString, $concatenatedIds);
    }

    // -- Ids as string

    /**
     * @test
     *
     * @covers ::idsAsStringList
     */
    public function id_list_as_string_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $orderedIdList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $expectedArray = [
            (string) $idAnton,
            (string) $idMarkus,
            (string) $idPaul,
            (string) $idTom,
        ];

        // -- Act & Assert
        self::assertSame($expectedArray, $orderedIdList->idsAsStringList());
    }
}

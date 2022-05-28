<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdAlreadyInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListsMustBeEqual;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\Ids\ValueObject\IdList */
final class IdListTest extends TestCase
{
    // -- Construct

    /**
     * @test
     * @covers ::__construct
     * @doesNotPerformAssertions
     */
    public function id_list_construction_works(): void
    {
        // -- Arrange & Act
        new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);
    }

    /**
     * @test
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
     * @covers ::merge
     */
    public function merge_works(): void
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
        $mergedIdList = UserIdList::merge([
            $idList1,
            $idList2,
        ]);

        // -- Assert
        self::assertCount(6, $mergedIdList);
    }

    /**
     * @test
     * @covers ::merge
     */
    public function merge_with_duplicates_works(): void
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
        $mergedIdList = UserIdList::merge([
            $idList1,
            $idList2,
        ]);

        // -- Assert
        self::assertCount(5, $mergedIdList);
    }

    // -- Add id

    /**
     * @test
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
     * @covers ::addId
     */
    public function add_id_fails_with_duplicate_id(): void
    {
        // -- Assert
        $this->expectException(IdAlreadyInList::class);

        // -- Arrange
        $existingUserId = UserId::generateRandom();
        $idList = new UserIdList([
            $existingUserId,
            UserId::generateRandom(),
        ]);

        // -- Act
        $idList->addId($existingUserId);
    }

    // -- Remove id

    /**
     * @test
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

    // -- Diff

    /**
     * @test
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
        $diffList = $originalList->diff($partialList);

        // -- Assert
        self::assertCount(2, $diffList);

        self::assertTrue($diffList->containsId($idMarkus));
        self::assertTrue($diffList->containsId($idTom));
    }

    // -- Contains

    /**
     * @test
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
        self::assertTrue($listWithAllIds->containsId($idAnton));
        self::assertFalse($partialList->containsId($idMarkus));

        self::assertTrue($partialList->notContainsId($idMarkus));
        self::assertFalse($listWithAllIds->notContainsId($idMarkus));
    }

    // -- Is equal and not equal

    /**
     * @test
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

        $originalList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $copyOfOriginalList = UserIdList::fromIds([
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
        self::assertTrue($originalList->isEqualTo($copyOfOriginalList));
        self::assertFalse($originalList->isEqualTo($partialList));

        self::assertTrue($originalList->isNotEqualTo($partialList));
        self::assertFalse($originalList->isNotEqualTo($copyOfOriginalList));
    }

    /**
     * @test
     * @covers ::mustBeEqualTo
     */
    public function must_not_be_equal_to(): void
    {
        // -- Assert
        $this->expectException(IdListsMustBeEqual::class);

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

    // -- Count

    /**
     * @test
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
     * @covers ::current
     * @covers ::next
     * @covers ::key
     * @covers ::rewind
     * @covers ::valid
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
    }

    /**
     * @test
     * @covers ::current
     * @covers ::next
     * @covers ::key
     * @covers ::rewind
     * @covers ::valid
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

    /**
     * @test
     * @covers ::isInSameOrder
     * @covers ::idAtPosition
     * @covers ::intersect
     */
    public function id_list_is_in_same_order_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        // Ordered alphabetically
        $orderedIdList = UserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        // In order but with missing ids
        $idListThatIsInOrder = UserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        $idListThatIsNotInOrder = UserIdList::fromIds([
            $idPaul,
            $idMarkus,
        ]);

        // -- Act & Assert
        self::assertTrue($idListThatIsInOrder->isInSameOrder($orderedIdList));
        self::assertFalse($idListThatIsNotInOrder->isInSameOrder($orderedIdList));
    }
}

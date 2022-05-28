<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListsMustBeEqual;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\Ids\ValueObject\IdList */
final class IdListTest extends TestCase
{
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
     * @covers ::current
     * @covers ::next
     * @covers ::key
     * @covers ::rewind
     * @covers ::valid
     */
    public function id_list_iteration_works(): void
    {
        // -- Arrange
        $investorId1 = UserId::generateRandom();
        $investorId2 = UserId::generateRandom();
        $investorId3 = UserId::generateRandom();

        $idList = new UserIdList([
            $investorId1,
            $investorId2,
            $investorId3,
        ]);

        $expectedString = sprintf(
            '%s%s%s',
            (string) $investorId1,
            (string) $investorId2,
            (string) $investorId3,
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
     * @covers ::current
     * @covers ::next
     * @covers ::key
     * @covers ::rewind
     * @covers ::valid
     */
    public function id_list_works_with_gaps_in_input_list(): void
    {
        // -- Arrange
        $investorId1 = UserId::generateRandom();
        $investorId2 = UserId::generateRandom();
        $investorId3 = UserId::generateRandom();

        $idList = new UserIdList([
            0 => $investorId1,
            1 => $investorId2,
            3 => $investorId3,
        ]);

        $expectedString = sprintf(
            '%s%s%s',
            (string) $investorId1,
            (string) $investorId2,
            (string) $investorId3,
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

    /**
     * @test
     * @covers ::isInSameOrder
     */
    public function id_list_is_in_same_order_works(): void
    {
        // -- Arrange
        $investorIdAnton = UserId::generateRandom();
        $investorIdMarkus = UserId::generateRandom();
        $investorIdPaul = UserId::generateRandom();
        $investorIdTom = UserId::generateRandom();

        // Ordered alphabetically
        $orderedIdList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdMarkus,
            $investorIdPaul,
            $investorIdTom,
        ]);

        // In order but with missing ids
        $idListThatIsInOrder = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdPaul,
        ]);

        $idListThatIsNotInOrder = UserIdList::fromIds([
            $investorIdPaul,
            $investorIdMarkus,
        ]);

        // -- Act & Assert
        self::assertTrue($idListThatIsInOrder->isInSameOrder($orderedIdList));
        self::assertFalse($idListThatIsNotInOrder->isInSameOrder($orderedIdList));
    }

    /**
     * @test
     * @covers ::emptyList
     */
    public function empty_list_works(): void
    {
        $emptyIdList = UserIdList::emptyList();

        self::assertCount(0, $emptyIdList);
    }

    /**
     * @test
     * @covers ::merge
     */
    public function merge_works(): void
    {
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

        $mergedIdList = UserIdList::merge([
            $idList1,
            $idList2,
        ]);

        self::assertCount(6, $mergedIdList);
    }

    /**
     * @test
     * @covers ::merge
     */
    public function merge_with_duplicates_works(): void
    {
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

        $mergedIdList = UserIdList::merge([
            $idList1,
            $idList2,
        ]);

        self::assertCount(5, $mergedIdList);
    }

    /**
     * @test
     * @covers ::diff
     */
    public function id_list_diff_works(): void
    {
        // -- Arrange
        $investorIdAnton = UserId::generateRandom();
        $investorIdMarkus = UserId::generateRandom();
        $investorIdPaul = UserId::generateRandom();
        $investorIdTom = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdMarkus,
            $investorIdPaul,
            $investorIdTom,
        ]);

        $partialList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdPaul,
        ]);

        // -- Act
        $diffList = $originalList->diff($partialList);

        // -- Assert
        self::assertCount(2, $diffList);

        self::assertTrue($diffList->containsId($investorIdMarkus));
        self::assertTrue($diffList->containsId($investorIdTom));
    }

    /**
     * @test
     * @covers ::isEqualTo
     */
    public function id_list_is_equal_to(): void
    {
        // -- Arrange
        $investorIdAnton = UserId::generateRandom();
        $investorIdMarkus = UserId::generateRandom();
        $investorIdPaul = UserId::generateRandom();
        $investorIdTom = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdMarkus,
            $investorIdPaul,
            $investorIdTom,
        ]);

        $copyOfOriginalList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdMarkus,
            $investorIdPaul,
            $investorIdTom,
        ]);

        $partialList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdPaul,
        ]);

        // -- Act & Assert
        self::assertTrue($originalList->isEqualTo($copyOfOriginalList));
        self::assertFalse($originalList->isEqualTo($partialList));
    }

    /**
     * @test
     * @covers ::mustBeEqualTo
     */
    public function must_not_be_equal_to(): void
    {
        $this->expectException(IdListsMustBeEqual::class);

        // -- Arrange
        $investorIdAnton = UserId::generateRandom();
        $investorIdMarkus = UserId::generateRandom();
        $investorIdPaul = UserId::generateRandom();
        $investorIdTom = UserId::generateRandom();

        $originalList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdMarkus,
            $investorIdPaul,
            $investorIdTom,
        ]);

        $partialList = UserIdList::fromIds([
            $investorIdAnton,
            $investorIdPaul,
        ]);

        // -- Act & Assert
        $originalList->mustBeEqualTo($partialList);
    }
}

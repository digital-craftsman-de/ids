<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\ValueObject\SymfonyUserId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\IdNotEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\InvalidId;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\Ids\ValueObject\Id */
final class SymfonyIdTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::fromString
     *
     * @doesNotPerformAssertions
     */
    public function construction_works(): void
    {
        // -- Act
        new SymfonyUserId('018da6bd-b268-704d-adb3-2af2112e3825');
        SymfonyUserId::fromString('018da6bd-b63f-7711-9217-152e96c54066');
        SymfonyUserId::generateRandom();
    }

    /**
     * @test
     *
     * @covers ::__construct
     */
    public function construction_with_invalid_id_fails(): void
    {
        // -- Assert
        $this->expectException(InvalidId::class);

        // -- Act
        new SymfonyUserId('test');
    }

    /**
     * @test
     *
     * @covers ::isEqualTo
     */
    public function user_id_is_equal(): void
    {
        // -- Arrange
        $symfonyUserId1 = SymfonyUserId::fromString('018da6bd-3512-7e94-8455-0a974335de5a');
        $symfonyUserId2 = SymfonyUserId::fromString('018da6bd-3512-7e94-8455-0a974335de5a');

        // -- Act & Assert
        self::assertTrue($symfonyUserId1->isEqualTo($symfonyUserId2));
    }

    /**
     * @test
     *
     * @covers ::isNotEqualTo
     */
    public function user_id_is_not_equal(): void
    {
        // -- Arrange
        $symfonyUserId1 = SymfonyUserId::generateRandom();
        $symfonyUserId2 = SymfonyUserId::generateRandom();

        // -- Act & Assert
        self::assertTrue($symfonyUserId1->isNotEqualTo($symfonyUserId2));
    }

    /**
     * @test
     *
     * @covers ::mustBeEqualTo
     *
     * @doesNotPerformAssertions
     */
    public function user_id_must_be_equal(): void
    {
        // -- Arrange
        $symfonyUserId1 = SymfonyUserId::generateRandom();
        $symfonyUserId2 = SymfonyUserId::fromString((string) $symfonyUserId1);

        // -- Act
        $symfonyUserId1->mustBeEqualTo($symfonyUserId2);
    }

    /**
     * @test
     *
     * @covers ::mustNotBeEqualTo
     *
     * @doesNotPerformAssertions
     */
    public function user_id_must_not_be_equal(): void
    {
        // -- Arrange
        $symfonyUserId1 = SymfonyUserId::generateRandom();
        $symfonyUserId2 = SymfonyUserId::generateRandom();

        // -- Act
        $symfonyUserId1->mustNotBeEqualTo($symfonyUserId2);
    }

    /**
     * @test
     *
     * @covers ::mustBeEqualTo
     */
    public function user_id_must_be_equal_fails(): void
    {
        // -- Assert
        $this->expectException(IdNotEqual::class);

        // -- Arrange
        $symfonyUserId1 = SymfonyUserId::generateRandom();
        $symfonyUserId2 = SymfonyUserId::generateRandom();

        // -- Act
        $symfonyUserId1->mustBeEqualTo($symfonyUserId2);
    }

    /**
     * @test
     *
     * @covers ::mustNotBeEqualTo
     */
    public function user_id_must_not_be_equal_fails(): void
    {
        // -- Assert
        $this->expectException(IdEqual::class);

        // -- Arrange
        $symfonyUserId1 = SymfonyUserId::generateRandom();
        $symfonyUserId2 = new SymfonyUserId((string) $symfonyUserId1);

        // -- Act
        $symfonyUserId1->mustNotBeEqualTo($symfonyUserId2);
    }
}

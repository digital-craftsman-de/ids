<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/** @coversDefaultClass \DigitalCraftsman\Ids\Serializer\IdListNormalizer */
final class IdListNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::normalize
     * @covers ::denormalize
     * @covers ::isValid
     */
    public function id_list_normalization_and_denormalization_works(): void
    {
        // -- Arrange
        $userIdList = new UserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $normalizer = new IdListNormalizer();

        // -- Act
        $normalizedData = $normalizer->normalize($userIdList);
        $denormalizedData = $normalizer->denormalize($normalizedData, UserIdList::class);

        // -- Assert
        self::assertEquals($userIdList, $denormalizedData);
    }

    /**
     * @test
     *
     * @covers ::denormalize
     */
    public function id_list_denormalization_with_null_works(): void
    {
        // -- Arrange
        $normalizer = new IdListNormalizer();

        // -- Act
        $denormalizedData = $normalizer->denormalize(null, UserIdList::class);

        // -- Assert
        self::assertNull($denormalizedData);
    }

    /**
     * @test
     *
     * @covers ::denormalize
     * @covers ::isValid
     */
    public function id_list_denormalization_fails_with_invalid_values(): void
    {
        // -- Assert
        $this->expectException(UnexpectedValueException::class);

        // -- Arrange
        $normalizer = new IdListNormalizer();

        $invalidData = [
            (string) UserId::generateRandom(),
            15,
        ];

        // -- Act
        /** @psalm-suppress InvalidScalarArgument */
        $normalizer->denormalize($invalidData, UserIdList::class);
    }

    /**
     * @test
     *
     * @covers ::supportsNormalization
     */
    public function supports_normalization(): void
    {
        // -- Arrange
        $userIdList = new UserIdList([]);

        $normalizer = new IdListNormalizer();

        // -- Act & Assert
        self::assertTrue($normalizer->supportsNormalization($userIdList));
    }

    /**
     * @test
     *
     * @covers ::supportsDenormalization
     */
    public function supports_denormalization(): void
    {
        // -- Arrange
        $idListData = [
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
        ];

        $normalizer = new IdListNormalizer();

        // -- Act & Assert
        self::assertTrue($normalizer->supportsDenormalization($idListData, UserIdList::class));
    }
}

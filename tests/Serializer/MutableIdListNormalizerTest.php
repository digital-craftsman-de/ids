<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\Test\ValueObject\MutableUserIdList;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/** @coversDefaultClass \DigitalCraftsman\Ids\Serializer\MutableIdListNormalizer */
final class MutableIdListNormalizerTest extends TestCase
{
    /**
     * @test
     * @covers ::normalize
     * @covers ::denormalize
     * @covers ::isValid
     */
    public function id_list_normalization_and_denormalization_works(): void
    {
        // -- Arrange
        $userIdList = new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $normalizer = new MutableIdListNormalizer();

        // -- Act
        $normalizedData = $normalizer->normalize($userIdList);
        $denormalizedData = $normalizer->denormalize($normalizedData, MutableUserIdList::class);

        // -- Assert
        self::assertEquals($userIdList, $denormalizedData);
    }

    /**
     * @test
     * @covers ::denormalize
     */
    public function id_list_denormalization_with_null_works(): void
    {
        // -- Arrange
        $normalizer = new MutableIdListNormalizer();

        // -- Act
        $denormalizedData = $normalizer->denormalize(null, MutableUserIdList::class);

        // -- Assert
        self::assertNull($denormalizedData);
    }

    /**
     * @test
     * @covers ::denormalize
     * @covers ::isValid
     */
    public function id_list_denormalization_fails_with_invalid_values(): void
    {
        // -- Assert
        $this->expectException(UnexpectedValueException::class);

        // -- Arrange
        $normalizer = new MutableIdListNormalizer();

        $invalidData = [
            (string) UserId::generateRandom(),
            15,
        ];

        // -- Act
        $normalizer->denormalize($invalidData, MutableUserIdList::class);
    }

    /**
     * @test
     * @covers ::supportsNormalization
     */
    public function supports_normalization(): void
    {
        // -- Arrange
        $userIdList = new MutableUserIdList([]);

        $normalizer = new MutableIdListNormalizer();

        // -- Act & Assert
        self::assertTrue($normalizer->supportsNormalization($userIdList));
    }

    /**
     * @test
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

        $normalizer = new MutableIdListNormalizer();

        // -- Act & Assert
        self::assertTrue($normalizer->supportsDenormalization($idListData, MutableUserIdList::class));
    }
}

<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\Ids\Serializer\IdNormalizer */
final class IdNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::normalize
     * @covers ::denormalize
     */
    public function id_normalization_and_denormalization_works(): void
    {
        // -- Arrange
        $userId = UserId::generateRandom();

        $normalizer = new IdNormalizer();

        // -- Act
        $normalizedData = $normalizer->normalize($userId);
        $denormalizedData = $normalizer->denormalize($normalizedData, UserId::class);

        // -- Assert
        self::assertEquals($userId, $denormalizedData);
    }

    /**
     * @test
     *
     * @covers ::denormalize
     */
    public function id_denormalization_with_null_works(): void
    {
        // -- Arrange
        $normalizer = new IdNormalizer();

        // -- Act
        $denormalizedData = $normalizer->denormalize(null, UserId::class);

        // -- Assert
        self::assertNull($denormalizedData);
    }

    /**
     * @test
     *
     * @covers ::supportsNormalization
     */
    public function supports_normalization(): void
    {
        // -- Arrange
        $userId = UserId::generateRandom();

        $normalizer = new IdNormalizer();

        // -- Act & Assert
        self::assertTrue($normalizer->supportsNormalization($userId));
    }

    /**
     * @test
     *
     * @covers ::supportsNormalization
     */
    public function supports_normalization_fails_with_invalid_data(): void
    {
        // -- Arrange
        $userId = 5;

        $normalizer = new IdNormalizer();

        // -- Act & Assert
        self::assertFalse($normalizer->supportsNormalization($userId));
    }

    /**
     * @test
     *
     * @covers ::supportsDenormalization
     */
    public function supports_denormalization(): void
    {
        // -- Arrange
        $userId = UserId::generateRandom();

        $normalizer = new IdNormalizer();

        // -- Act & Assert
        self::assertTrue($normalizer->supportsDenormalization((string) $userId, UserId::class));
    }
}

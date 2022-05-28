<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class IdNormalizerTest extends TestCase
{
    /** @test */
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
}

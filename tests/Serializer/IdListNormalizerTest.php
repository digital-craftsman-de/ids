<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use PHPUnit\Framework\TestCase;

final class IdListNormalizerTest extends TestCase
{
    /** @test */
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
}

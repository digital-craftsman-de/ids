<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(IdListNormalizer::class)]
final class IdListNormalizerTest extends TestCase
{
    #[Test]
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

    #[Test]
    public function id_list_denormalization_with_null_works(): void
    {
        // -- Arrange
        $normalizer = new IdListNormalizer();

        // -- Act
        $denormalizedData = $normalizer->denormalize(null, UserIdList::class);

        // -- Assert
        self::assertNull($denormalizedData);
    }

    #[Test]
    public function supports_normalization_for_list(): void
    {
        // -- Arrange
        $userIdList = new UserIdList([]);

        $normalizer = new IdListNormalizer();

        // -- Act & Assert
        self::assertTrue($normalizer->supportsNormalization($userIdList));
    }

    #[Test]
    public function supports_normalization_fails_with_wrong_data(): void
    {
        // -- Arrange
        $userId = UserId::generateRandom();

        $normalizer = new IdListNormalizer();

        // -- Act & Assert
        self::assertFalse($normalizer->supportsNormalization($userId));
    }

    #[Test]
    public function supports_denormalization_for_id_list(): void
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

    #[Test]
    public function supports_denormalization_with_array_of_ids(): void
    {
        // -- Arrange
        $idListData = [
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
        ];

        $normalizer = new IdListNormalizer();

        // -- Act & Assert
        self::assertFalse($normalizer->supportsDenormalization($idListData, sprintf('%s[]', UserId::class)));
    }

    #[Test]
    public function supports_denormalization_with_wrong_type(): void
    {
        // -- Arrange
        $idListData = [
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
            (string) UserId::generateRandom(),
        ];

        $normalizer = new IdListNormalizer();

        // -- Act & Assert
        self::assertFalse($normalizer->supportsDenormalization($idListData, UserId::class));
    }
}

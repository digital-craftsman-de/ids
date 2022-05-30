<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\Id;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IdNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Id;
    }

    /** @param class-string $type */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_subclass_of($type, Id::class);
    }

    /** @param Id $object */
    public function normalize($object, $format = null, array $context = []): string
    {
        return (string) $object;
    }

    /**
     * @param ?string $data
     * @psalm-param class-string<Id> $type
     */
    public function denormalize($data, $type, $format = null, array $context = []): ?Id
    {
        if ($data === null) {
            return null;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return $type::fromString($data);
    }

    /** @codeCoverageIgnore */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

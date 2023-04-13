<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\Id;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IdNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    /** @param array<string, string|int|boolean> $context */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Id;
    }

    /**
     * @param string                            $type
     * @param array<string, string|int|boolean> $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return is_subclass_of($type, Id::class);
    }

    /**
     * @param Id                                $object
     * @param array<string, string|int|boolean> $context
     */
    public function normalize($object, $format = null, array $context = []): string
    {
        return (string) $object;
    }

    /**
     * @param ?string                           $data
     * @param class-string<Id>                  $type
     * @param array<string, string|int|boolean> $context
     */
    public function denormalize($data, $type, $format = null, array $context = []): ?Id
    {
        if ($data === null) {
            return null;
        }

        return $type::fromString($data);
    }

    /** @codeCoverageIgnore */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

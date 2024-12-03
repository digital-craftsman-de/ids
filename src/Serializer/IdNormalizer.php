<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\Id;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class IdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @param array<string, string|int|bool> $context */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Id;
    }

    /**
     * @param string                         $type
     * @param array<string, string|int|bool> $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return is_subclass_of($type, Id::class);
    }

    /**
     * @param Id                             $data
     * @param array<string, string|int|bool> $context
     */
    public function normalize($data, $format = null, array $context = []): string
    {
        return (string) $data;
    }

    /**
     * @param ?string                        $data
     * @param class-string<Id>               $type
     * @param array<string, string|int|bool> $context
     */
    public function denormalize($data, $type, $format = null, array $context = []): ?Id
    {
        if ($data === null) {
            return null;
        }

        return $type::fromString($data);
    }

    /**
     * @return array<class-string, bool>
     *
     * @codeCoverageIgnore
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Id::class => true,
        ];
    }
}

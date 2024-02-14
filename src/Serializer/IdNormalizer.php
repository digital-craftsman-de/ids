<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\IdInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class IdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @param array<string, string|int|bool> $context */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof IdInterface;
    }

    /**
     * @param string                         $type
     * @param array<string, string|int|bool> $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return is_subclass_of($type, IdInterface::class);
    }

    /**
     * @param IdInterface                    $object
     * @param array<string, string|int|bool> $context
     */
    public function normalize($object, $format = null, array $context = []): string
    {
        return (string) $object;
    }

    /**
     * @param ?string                        $data
     * @param class-string<IdInterface>      $type
     * @param array<string, string|int|bool> $context
     */
    public function denormalize($data, $type, $format = null, array $context = []): ?IdInterface
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
            IdInterface::class => true,
        ];
    }
}

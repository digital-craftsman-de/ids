<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\IdList;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IdListNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param IdList|object                     $data
     * @param array<string, string|int|boolean> $context
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof IdList;
    }

    /**
     * @param class-string                      $type
     * @param array<string, string|int|boolean> $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return class_exists($type)
            && get_parent_class($type) === IdList::class;
    }

    /**
     * @param IdList                            $object
     * @param array<string, string|int|boolean> $context
     *
     * @return array<int, string>
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return $object->idsAsStringList();
    }

    /**
     * @param ?array<int, string>               $data
     * @param class-string<IdList>              $type
     * @param array<string, string|int|boolean> $context
     */
    public function denormalize($data, $type, $format = null, array $context = []): ?IdList
    {
        if ($data === null) {
            return null;
        }

        $idClass = $type::handlesIdClass();

        $ids = [];
        foreach ($data as $string) {
            $ids[] = new $idClass($string);
        }

        return new $type($ids);
    }

    /** @codeCoverageIgnore */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

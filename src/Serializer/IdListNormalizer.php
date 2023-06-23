<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\IdList;
use DigitalCraftsman\Ids\ValueObject\OrderedIdList;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class IdListNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param IdList|OrderedIdList|object       $data
     * @param array<string, string|int|boolean> $context
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof IdList
            || $data instanceof OrderedIdList;
    }

    /**
     * @param string                            $type
     * @param array<string, string|int|boolean> $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        if (!class_exists($type)) {
            return false;
        }

        $parentClass = get_parent_class($type);

        return $parentClass === IdList::class
            || $parentClass === OrderedIdList::class;
    }

    /**
     * @param IdList|OrderedIdList              $object
     * @param array<string, string|int|boolean> $context
     *
     * @return array<int, string>
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return $object->idsAsStringList();
    }

    /**
     * @param ?array<int, string>                $data
     * @param class-string<IdList|OrderedIdList> $type
     * @param array<string, string|int|boolean>  $context
     */
    public function denormalize($data, $type, $format = null, array $context = []): IdList|OrderedIdList|null
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

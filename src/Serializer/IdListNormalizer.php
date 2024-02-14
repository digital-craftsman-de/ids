<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\IdList;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class IdListNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param IdList|object                  $data
     * @param array<string, string|int|bool> $context
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof IdList;
    }

    /**
     * @param string                         $type
     * @param array<string, string|int|bool> $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        if (!class_exists($type)) {
            return false;
        }

        $parentClass = get_parent_class($type);

        return $parentClass === IdList::class;
    }

    /**
     * @param IdList                         $object
     * @param array<string, string|int|bool> $context
     *
     * @return array<int, string>
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return $object->idsAsStringList();
    }

    /**
     * @param ?array<int, string>            $data
     * @param class-string<IdList>           $type
     * @param array<string, string|int|bool> $context
     */
    public function denormalize($data, $type, $format = null, array $context = []): IdList|null
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

    /**
     * @return array<class-string, bool>
     *
     * @codeCoverageIgnore
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            IdList::class => true,
        ];
    }
}

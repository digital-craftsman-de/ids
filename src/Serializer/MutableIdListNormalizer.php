<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Serializer;

use DigitalCraftsman\Ids\ValueObject\Id;
use DigitalCraftsman\Ids\ValueObject\MutableIdList;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MutableIdListNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    /** @param MutableIdList|object $data */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof MutableIdList;
    }

    /** @param class-string $type */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return class_exists($type)
            && get_parent_class($type) === MutableIdList::class;
    }

    /** @param MutableIdList $object */
    public function normalize($object, $format = null, array $context = []): array
    {
        return $object->idsAsStringList();
    }

    /**
     * @param ?array<int, string>         $data
     * @param class-string<MutableIdList> $type
     */
    public function denormalize($data, $type, $format = null, array $context = []): ?MutableIdList
    {
        if ($data === null) {
            return null;
        }

        if (!$this->isValid($data)) {
            throw new UnexpectedValueException('Expected a valid list.');
        }

        $idClass = $type::handlesIdClass();

        /** @var array<int, Id> $ids */
        $ids = array_map(
            static fn (string $id) => new $idClass($id),
            $data,
        );

        return $type::fromIds($ids);
    }

    /** @codeCoverageIgnore */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Uuid::isValid($string) is not in here on purpose as the Id object itself calls that method on construction.
     *
     * @param array<int, string> $data
     */
    private function isValid(array $data): bool
    {
        foreach ($data as $string) {
            if (!is_string($string)) {
                return false;
            }
        }

        return true;
    }
}

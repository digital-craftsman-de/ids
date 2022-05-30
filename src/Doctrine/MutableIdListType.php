<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\ValueObject\BaseId;
use DigitalCraftsman\Ids\ValueObject\MutableIdList;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class MutableIdListType extends Type
{
    abstract protected function getTypeName(): string;

    /** @psalm-return class-string<MutableIdList> */
    abstract protected function getIdListClass(): string;

    /** @psalm-return class-string<BaseId> */
    abstract protected function getIdClass(): string;

    /** @codeCoverageIgnore */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /** @param ?MutableIdList $value */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value->idsAsStringList(), JSON_THROW_ON_ERROR);
    }

    /** @param ?string $value */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?object
    {
        if ($value === null) {
            return null;
        }

        $idListClass = $this->getIdListClass();
        $idClass = $this->getIdClass();

        /** @var array<int, string> $idStrings */
        $idStrings = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        $ids = [];
        foreach ($idStrings as $idString) {
            /** @noinspection PhpUndefinedMethodInspection */
            $ids[] = new $idClass($idString);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return $idListClass::fromIds($ids);
    }

    /** @codeCoverageIgnore */
    public function getName(): string
    {
        return $this->getTypeName();
    }

    /** @codeCoverageIgnore */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

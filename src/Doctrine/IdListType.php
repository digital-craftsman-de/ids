<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\ValueObject\Id;
use DigitalCraftsman\Ids\ValueObject\IdList;
use DigitalCraftsman\Ids\ValueObject\OrderedIdList;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class IdListType extends Type
{
    abstract public static function getTypeName(): string;

    /** @psalm-return class-string<IdList|OrderedIdList> */
    abstract public static function getClass(): string;

    /** @psalm-return class-string<Id> */
    abstract public static function getIdClass(): string;

    /** @codeCoverageIgnore */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /** @param IdList|OrderedIdList|null $value */
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

        $idListClass = static::getClass();
        $idClass = static::getIdClass();

        /** @var array<int, string> $idStrings */
        $idStrings = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        $ids = [];
        foreach ($idStrings as $idString) {
            $ids[] = new $idClass($idString);
        }

        return new $idListClass($ids);
    }

    /** @codeCoverageIgnore */
    public function getName(): string
    {
        return static::getTypeName();
    }

    /** @codeCoverageIgnore */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

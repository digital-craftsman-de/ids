<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\ValueObject\BaseId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class BaseIdType extends Type
{
    abstract protected function getTypeName(): string;

    /** @return class-string<BaseId> */
    abstract protected function getIdClass(): string;

    /** @codeCoverageIgnore */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    /** @param ?BaseId $value */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    /** @param ?string $value */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?BaseId
    {
        if ($value === null) {
            return null;
        }

        return $this->getIdClass()::fromString($value);
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

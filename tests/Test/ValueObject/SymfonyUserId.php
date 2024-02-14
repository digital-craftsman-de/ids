<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Test\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Id;
use DigitalCraftsman\Ids\ValueObject\SymfonyId;
use Symfony\Component\Uid\UuidV7;

readonly class SymfonyUserId extends SymfonyId
{
    protected static function getClass(): string
    {
        return UuidV7::class;
    }
}

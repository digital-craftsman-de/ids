<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\DuplicateIds;

abstract class MutableIdList implements \Iterator, \Countable
{
    /**
     * The id type has to be overwritten in the child class so that the correct id type is used in denormalization.
     *
     * @var array<int, BaseId>
     */
    public array $ids;

    public int $index = 0;

    /**
     * The optional parameter $withValidation is only here to be used while in normalization to improve performance and
     * must never be used when creating ids at any other point.
     *
     * @param array<int, BaseId> $ids
     */
    final public function __construct(
        array $ids = [],
    ) {
        self::mustNotContainDuplicateIds($ids);

        $this->ids = array_values($ids);
    }

    // Mutation

    public function addId(BaseId $id): void
    {
        if (in_array($id, $this->ids, false)) {
            return;
        }

        $this->ids[] = $id;
    }

    public function removeId(BaseId $id): void
    {
        $this->ids = array_values(array_filter($this->ids, static function (BaseId $currentId) use ($id) {
            return $currentId->isNotEqualTo($id);
        }));
    }

    // Validation

    public function containsId(BaseId $baseId): bool
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return $baseId->isExistingInList($this->ids);
    }

    public function notContainsId(BaseId $baseId): bool
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return $baseId->isNotExistingInList($this->ids);
    }

    /** @throws DuplicateIds */
    public static function mustNotContainDuplicateIds(array $ids): void
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($ids != array_unique($ids)) {
            throw new DuplicateIds();
        }
    }

    // Accessors

    /** @return array<int, string> */
    public function idsAsStringList(): array
    {
        $ids = [];
        foreach ($this->ids as $id) {
            $ids[] = (string) $id;
        }

        return $ids;
    }

    // -- Iterator

    public function current(): BaseId
    {
        return $this->ids[$this->index];
    }

    public function next(): void
    {
        ++$this->index;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return array_key_exists($this->index, $this->ids);
    }

    // -- Countable

    public function count(): int
    {
        return count($this->ids);
    }
}

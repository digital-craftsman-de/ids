<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\DuplicateIds;
use DigitalCraftsman\Ids\ValueObject\Exception\IdAlreadyInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesNotContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListIsNotEmpty;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListsMustBeEqual;

abstract class MutableIdList implements \Iterator, \Countable
{
    /**
     * The id type has to be overwritten in the child class so that the correct id type is used in denormalization.
     *
     * @var array<int, BaseId>
     */
    public array $ids;

    public int $index = 0;

    // -- Construction

    /**
     * The optional parameter $withValidation is only here to be used while in normalization to improve performance and
     * must never be used when creating ids at any other point.
     *
     * @param array<int, BaseId> $ids
     */
    final public function __construct(
        array $ids,
    ) {
        self::mustNotContainDuplicateIds($ids);

        $this->ids = array_values($ids);
    }

    /** @param array<int, BaseId> $ids */
    final public static function fromIds(array $ids): static
    {
        return new static($ids);
    }

    final public static function emptyList(): static
    {
        return new static([]);
    }

    /**
     * Ids that are available in more than one list, are only added once.
     *
     * @param array<int, static> $idLists
     */
    final public static function fromIdLists(array $idLists): static
    {
        $ids = [];
        foreach ($idLists as $idList) {
            foreach ($idList as $id) {
                $ids[] = $id;
            }
        }

        $uniqueIds = array_unique($ids);

        return new static($uniqueIds);
    }

    // -- Configuration

    abstract public static function handlesIdClass(): string;

    // -- Transformers

    public function addId(BaseId $id): void
    {
        if ($this->containsId($id)) {
            throw new IdAlreadyInList($id);
        }

        $this->ids[] = $id;
    }

    public function addIdWhenNotInList(BaseId $id): void
    {
        if ($this->notContainsId($id)) {
            $this->ids[] = $id;
        }
    }

    public function removeId(BaseId $id): void
    {
        $this->ids = array_values(array_filter(
            $this->ids,
            static fn (BaseId $currentId) => $currentId->isNotEqualTo($id),
        ));
    }

    public function diff(self $idList): void
    {
        $idsNotInList = [];
        foreach ($this->ids as $id) {
            if ($idList->notContainsId($id)) {
                $idsNotInList[] = $id;
            }
        }

        $this->ids = $idsNotInList;
    }

    /**
     * Returns an id list of ids which exist in both lists. The order of the new list is the same as the list on which the function is
     * triggered from. The supplied list is only used for validation and not for order.
     */
    public function intersect(self $idList): void
    {
        $idsInList = [];
        foreach ($this->ids as $id) {
            if ($idList->containsId($id)) {
                $idsInList[] = $id;
            }
        }

        $this->ids = $idsInList;
    }

    /**
     * Psalm doesn't yet realize when a function is pure and when not. To prevent us from marking every single use by hand (which will
     * reduce the readability), we ignore the purity for now and will change the call here to pure-callable as soon as Psalm can handle
     * it.
     *
     * @template R
     *
     * @psalm-param impure-Closure(BaseId):R $mapFunction
     *
     * @return array<int, R>
     */
    public function map(\Closure $mapFunction): array
    {
        /** @psalm-suppress ImpureFunctionCall */
        return array_values(array_map($mapFunction, $this->ids));
    }

    // -- Accessors

    /** @return array<int, string> */
    public function idsAsStringList(): array
    {
        $ids = [];
        foreach ($this->ids as $id) {
            $ids[] = (string) $id;
        }

        return $ids;
    }

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

    public function isEqualTo(self $idList): bool
    {
        foreach ($this->ids as $id) {
            if ($idList->notContainsId($id)) {
                return false;
            }
        }

        return true;
    }

    public function isNotEqualTo(self $idList): bool
    {
        return !$this->isEqualTo($idList);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }

    public function isInSameOrder(self $orderedList): bool
    {
        $orderedIds = [];
        foreach ($orderedList as $id) {
            if ($this->containsId($id)) {
                $orderedIds[] = $id;
            }
        }

        $orderedListWithIdenticalIds = new static($orderedIds);

        foreach ($this->ids as $index => $id) {
            if ($orderedListWithIdenticalIds->idAtPosition($index)->isNotEqualTo($id)) {
                return false;
            }
        }

        return true;
    }

    public function idAtPosition(int $position): BaseId
    {
        return $this->ids[$position];
    }

    // -- Guards

    /** @throws IdListDoesNotContainId */
    public function mustContainId(BaseId $id): void
    {
        if ($this->notContainsId($id)) {
            throw new IdListDoesNotContainId($id);
        }
    }

    /** @throws IdListDoesContainId */
    public function mustNotContainId(BaseId $id): void
    {
        if ($this->containsId($id)) {
            throw new IdListDoesContainId($id);
        }
    }

    /** @throws IdListIsNotEmpty */
    public function mustBeEmpty(): void
    {
        if ($this->isNotEmpty()) {
            throw new IdListIsNotEmpty();
        }
    }

    /** @throws DuplicateIds */
    public static function mustNotContainDuplicateIds(array $ids): void
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($ids != array_unique($ids)) {
            throw new DuplicateIds();
        }
    }

    /** @param static $idList */
    public function mustBeEqualTo(self $idList): void
    {
        if ($this->isNotEqualTo($idList)) {
            throw new IdListsMustBeEqual();
        }
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

<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\DuplicateIds;
use DigitalCraftsman\Ids\ValueObject\Exception\IdAlreadyInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdClassNotHandledInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesNotContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListIsNotEmpty;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListsMustBeEqual;

/**
 * @template T extends Id
 * @psalm-consistent-constructor
 *
 * I think Psalm has an issue here as the constructor is final and the parameter is the template the child class will extend from, but I
 * didn't find a solution without suppressing this psalm check.
 *
 * @psalm-suppress UnsafeGenericInstantiation
 */
abstract class IdList implements \Iterator, \Countable
{
    /**
     * @var array<int, Id>
     * @psalm-var array<int, T>
     * @psalm-readonly
     */
    public array $ids;

    public int $index = 0;

    // -- Construction

    /**
     * @param array<int, Id> $ids
     * @psalm-param array<int, T> $ids
     */
    final public function __construct(
        array $ids,
    ) {
        self::mustNotContainDuplicateIds($ids);
        self::mustOnlyContainIdsOfHandledClass($ids);

        $this->ids = array_values($ids);
    }

    /**
     * @template TT of T
     *
     * @param array<int, Id> $ids
     * @psalm-param array<int, TT> $ids
     */
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

    /** @psalm-param T $id */
    public function addId(Id $id): static
    {
        if ($this->containsId($id)) {
            throw new IdAlreadyInList($id);
        }

        $ids = $this->ids;
        $ids[] = $id;

        return new static($ids);
    }

    /** @psalm-param T $id */
    public function addIdWhenNotInList(Id $id): static
    {
        if ($this->containsId($id)) {
            return new static($this->ids);
        }

        $ids = $this->ids;
        $ids[] = $id;

        return new static($ids);
    }

    /** @psalm-param T $id */
    public function removeId(Id $id): static
    {
        $ids = array_filter(
            $this->ids,
            static fn (Id $currentId) => $currentId->isNotEqualTo($id),
        );

        return new static($ids);
    }

    /** @param static $idList */
    public function diff(self $idList): static
    {
        $idsNotInList = [];
        foreach ($this->ids as $id) {
            if ($idList->notContainsId($id)) {
                $idsNotInList[] = $id;
            }
        }
        foreach ($idList as $id) {
            if ($this->notContainsId($id)) {
                $idsNotInList[] = $id;
            }
        }

        $uniqueIds = array_unique($idsNotInList, SORT_STRING);

        return new static($uniqueIds);
    }

    /**
     * Returns an id list of ids which exist in both lists. The order of the new list is the same as the list on which the function is
     * triggered from. The supplied list is only used for validation and not for order.
     *
     * @param static $idList
     */
    public function intersect(self $idList): static
    {
        $idsInList = [];
        foreach ($this->ids as $id) {
            if ($idList->containsId($id)) {
                $idsInList[] = $id;
            }
        }

        return new static($idsInList);
    }

    /**
     * Psalm doesn't yet realize when a function is pure and when not. To prevent us from marking every single use by hand (which will
     * reduce the readability), we ignore the purity for now and will change the call here to pure-callable as soon as Psalm can handle
     * it.
     *
     * @template R
     *
     * @psalm-param impure-Closure(T):R $mapFunction
     *
     * @return array<int, R>
     */
    public function map(\Closure $mapFunction): array
    {
        /** @psalm-suppress ImpureFunctionCall */
        return array_values(array_map($mapFunction, $this->ids));
    }

    /** @psalm-param impure-Closure(T) $filterFunction */
    public function filter(\Closure $filterFunction): static
    {
        $filteredIds = array_filter(
            $this->ids,
            $filterFunction,
        );

        return new static($filteredIds);
    }

    /** @psalm-param impure-Closure(T) $everyFunction */
    public function every(\Closure $everyFunction): bool
    {
        foreach ($this->ids as $id) {
            if ($everyFunction($id) === false) {
                return false;
            }
        }

        return true;
    }

    /** @psalm-param impure-Closure(T) $someFunction */
    public function some(\Closure $someFunction): bool
    {
        foreach ($this->ids as $id) {
            if ($someFunction($id) === true) {
                return true;
            }
        }

        return false;
    }

    // -- Accessors

    /** @psalm-param T $id */
    public function containsId(Id $baseId): bool
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return $baseId->isExistingInList($this->ids);
    }

    /** @psalm-param T $id */
    public function notContainsId(Id $baseId): bool
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return $baseId->isNotExistingInList($this->ids);
    }

    /** @param static $idList */
    public function isEqualTo(self $idList): bool
    {
        if ($this->count() !== $idList->count()) {
            return false;
        }

        foreach ($this->ids as $id) {
            if ($idList->notContainsId($id)) {
                return false;
            }
        }

        return true;
    }

    /** @param static $idList */
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

    /** @param static $orderedList */
    public function isInSameOrder(self $orderedList): bool
    {
        $orderedListWithIdenticalIds = $orderedList->intersect($this);
        foreach ($this->ids as $index => $id) {
            if ($orderedListWithIdenticalIds->idAtPosition($index)->isNotEqualTo($id)) {
                return false;
            }
        }

        return true;
    }

    /** @psalm-return T */
    public function idAtPosition(int $position): Id
    {
        return $this->ids[$position];
    }

    /** @return array<int, string> */
    public function idsAsStringList(): array
    {
        $ids = [];
        foreach ($this->ids as $id) {
            $ids[] = (string) $id;
        }

        return $ids;
    }

    // -- Guards

    /**
     * @psalm-param T $id
     *
     * @throws IdListDoesNotContainId
     */
    public function mustContainId(Id $id): void
    {
        if ($this->notContainsId($id)) {
            throw new IdListDoesNotContainId($id);
        }
    }

    /**
     * @psalm-param T $id
     *
     * @throws IdListDoesContainId
     */
    public function mustNotContainId(Id $id): void
    {
        if ($this->containsId($id)) {
            throw new IdListDoesContainId($id);
        }
    }

    /**
     * @psalm-param T $id
     *
     * @throws IdListIsNotEmpty
     */
    public function mustBeEmpty(): void
    {
        if ($this->isNotEmpty()) {
            throw new IdListIsNotEmpty();
        }
    }

    /**
     * @template TT of T
     *
     * @param array<int, Id> $ids
     * @psalm-param array<int, TT> $ids
     *
     * @throws DuplicateIds
     */
    public static function mustNotContainDuplicateIds(array $ids): void
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        if (count($ids) != count(array_unique($ids))) {
            throw new DuplicateIds();
        }
    }

    /**
     * @template TT of T
     *
     * @param array<int, Id> $ids
     * @psalm-param array<int, TT> $ids
     *
     * @throws IdClassNotHandledInList
     */
    public static function mustOnlyContainIdsOfHandledClass(array $ids): void
    {
        $idClass = static::handlesIdClass();
        foreach ($ids as $id) {
            if ($id::class !== $idClass) {
                throw new IdClassNotHandledInList(static::class, $id::class);
            }
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

    /** @psalm-return T */
    public function current(): Id
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

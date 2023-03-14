<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\IdAlreadyInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdClassNotHandledInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesNotContainEveryId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesNotContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesNotContainSomeIds;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListIsNotEmpty;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListsMustBeEqual;

/**
 * @template T extends Id
 *
 * @template-implements \IteratorAggregate<int, T>
 *
 * @psalm-consistent-constructor
 *
 * I think Psalm has an issue here as the constructor is final and the parameter is the template the child class will extend from, but I
 * didn't find a solution without suppressing this psalm check.
 *
 * @psalm-suppress UnsafeGenericInstantiation
 */
abstract class IdList implements \IteratorAggregate, \Countable
{
    /** @var array<string, T> */
    public readonly array $ids;

    // -- Construction

    /** @param array<array-key, T> $ids */
    final public function __construct(
        array $ids,
    ) {
        self::mustOnlyContainIdsOfHandledClass($ids);

        $idsWithKeys = [];
        foreach ($ids as $id) {
            $idsWithKeys[$id->value] = $id;
        }

        $this->ids = $idsWithKeys;
    }

    /**
     * @template TT of T
     *
     * @param array<array-key, Id> $ids
     *
     * @psalm-param array<array-key, TT> $ids
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
     * @param array<array-key, static> $idLists
     */
    final public static function fromIdLists(array $idLists): static
    {
        // TODO: Improve merging
        $ids = [];
        foreach ($idLists as $idList) {
            foreach ($idList as $id) {
                $ids[] = $id;
            }
        }

        // No unique check necessary, as duplicate ids will be overwritten anyway

        return new static($ids);
    }

    // -- Configuration

    /**
     * @template TT of T
     *
     * @return class-string<TT>
     *
     * @phpstan-return class-string<T>
     */
    abstract public static function handlesIdClass(): string;

    // -- Transformers

    /** @param T $id */
    public function addId(Id $id): static
    {
        if ($this->containsId($id)) {
            throw new IdAlreadyInList($id);
        }

        $ids = $this->ids;
        $ids[] = $id;

        return new static($ids);
    }

    /** @param T $id */
    public function addIdWhenNotInList(Id $id): static
    {
        if ($this->containsId($id)) {
            return $this;
        }

        $ids = $this->ids;
        $ids[] = $id;

        return new static($ids);
    }

    /** @param T $id */
    public function removeId(Id $id): static
    {
        $existingIds = $this->ids;
        // TODO: Check if the element exists. Wasn't there before
        unset($existingIds[(string) $id]);

        return new static($existingIds);
    }

    /** @param static $idList */
    public function diff(self $idList): static
    {
        $idsNotInList = array_diff($this->ids, $idList->ids);

        return new static($idsNotInList);
    }

    /**
     * Returns an id list of ids which exist in both lists. The order of the new list is the same as the list on which the function is
     * triggered from. The supplied list is only used for validation and not for order.
     *
     * @param static $idList
     */
    public function intersect(self $idList): static
    {
        // TODO: Improve with key access
        $idsInList = [];
        foreach ($this->ids as $id) {
            if ($idList->containsId($id)) {
                $idsInList[] = $id;
            }
        }

        return new static($idsInList);
    }

    // -- Functional programming

    /**
     * Psalm doesn't yet realize when a function is pure and when not. To prevent us from marking every single use by hand (which will
     * reduce the readability), we ignore the purity for now and will change the call here to pure-callable as soon as Psalm can handle
     * it.
     *
     * @template R
     *
     * @psalm-param callable(T):R $mapFunction
     *
     * @return array<int, R>
     */
    public function map(callable $mapFunction): array
    {
        /** @psalm-suppress ImpureFunctionCall */
        return array_map($mapFunction, array_values($this->ids));
    }

    /** @psalm-param callable(T):bool $filterFunction */
    public function filter(callable $filterFunction): static
    {
        return new static(array_filter(
            $this->ids,
            $filterFunction,
        ));
    }

    /** @psalm-param callable(T):bool $everyFunction */
    public function every(callable $everyFunction): bool
    {
        foreach ($this->ids as $id) {
            if ($everyFunction($id) === false) {
                return false;
            }
        }

        return true;
    }

    /** @psalm-param callable(T):bool $someFunction */
    public function some(callable $someFunction): bool
    {
        foreach ($this->ids as $id) {
            if ($someFunction($id) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template R
     *
     * @psalm-param callable(R $carry, T):R $reduceFunction
     * @psalm-param R $initial
     *
     * @return R
     */
    public function reduce(callable $reduceFunction, mixed $initial = null): mixed
    {
        return array_reduce($this->ids, $reduceFunction, $initial);
    }

    // -- Accessors

    /** @param T $id */
    public function containsId(Id $id): bool
    {
        return array_key_exists((string) $id, $this->ids);
    }

    /** @param T $id */
    public function notContainsId(Id $id): bool
    {
        return !array_key_exists((string) $id, $this->ids);
    }

    public function containsEveryId(self $idList): bool
    {
        foreach ($idList as $id) {
            if ($this->notContainsId($id)) {
                return false;
            }
        }

        return true;
    }

    public function containsSomeIds(self $idList): bool
    {
        foreach ($idList as $id) {
            if ($this->containsId($id)) {
                return true;
            }
        }

        return false;
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

    /** @return array<int, string> */
    public function idsAsStringList(): array
    {
        return array_keys($this->ids);
    }

    // -- Guards

    /**
     * @param T $id
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
     * @param T $id
     *
     * @throws IdListDoesContainId
     */
    public function mustNotContainId(Id $id): void
    {
        if ($this->containsId($id)) {
            throw new IdListDoesContainId($id);
        }
    }

    /** @throws IdListDoesNotContainEveryId */
    public function mustContainEveryId(self $idList): void
    {
        if (!$this->containsEveryId($idList)) {
            throw new IdListDoesNotContainEveryId();
        }
    }

    /** @throws IdListDoesNotContainSomeIds */
    public function mustContainSomeIds(self $idList): void
    {
        if (!$this->containsSomeIds($idList)) {
            throw new IdListDoesNotContainSomeIds();
        }
    }

    /**
     * @param T $id
     *
     * @throws IdListIsNotEmpty
     */
    public function mustBeEmpty(): void
    {
        if ($this->isNotEmpty()) {
            throw new IdListIsNotEmpty();
        }
    }

    /** @param static $idList */
    public function mustBeEqualTo(self $idList): void
    {
        if ($this->isNotEqualTo($idList)) {
            throw new IdListsMustBeEqual();
        }
    }

    /**
     * @template TT of T
     *
     * @param array<int, Id> $ids
     *
     * @psalm-param array<int, TT> $ids
     *
     * @throws IdClassNotHandledInList
     */
    private static function mustOnlyContainIdsOfHandledClass(array $ids): void
    {
        $idClass = static::handlesIdClass();
        foreach ($ids as $id) {
            if (!$id instanceof $idClass) {
                throw new IdClassNotHandledInList(static::class, $id::class);
            }
        }
    }

    // -- Iterator aggregate

    /** @return \Iterator<int, T> */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator(array_values($this->ids));
    }

    // -- Countable

    public function count(): int
    {
        return count($this->ids);
    }
}

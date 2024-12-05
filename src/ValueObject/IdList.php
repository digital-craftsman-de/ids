<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\SelfAwareNormalizers\Serializer\ArrayNormalizable;

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
 *
 * @psalm-type NormalizedIdList = list<string>
 */
abstract readonly class IdList implements \IteratorAggregate, \Countable, ArrayNormalizable
{
    /**
     * @var array<string, T>
     */
    public array $ids;

    // -- Construction

    /**
     * @param array<array-key, T> $ids
     */
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

    /**
     * @param array<int, string> $idStrings
     */
    final public static function fromIdStrings(array $idStrings): static
    {
        $idClass = static::handlesIdClass();

        $ids = [];
        foreach ($idStrings as $idString) {
            $ids[] = new $idClass($idString);
        }

        return new static($ids);
    }

    /**
     * @template TT of T
     *
     * @param callable(mixed):Id $mapFunction
     *
     * @psalm-param callable(mixed):TT $mapFunction
     */
    public static function fromMap(
        iterable $items,
        callable $mapFunction,
    ): static {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $mapFunction($item);
        }

        return new static($ids);
    }

    final public static function emptyList(): static
    {
        return new static([]);
    }

    /**
     * Ids that are available in more than one list, are only added once.
     *
     * @param array<static> $idLists
     *
     * @psalm-param array<array-key, static> $idLists
     */
    final public static function fromIdLists(array $idLists): static
    {
        // No unique check necessary, as duplicate ids will be overwritten anyway
        $ids = [];
        foreach ($idLists as $idList) {
            foreach ($idList->ids as $key => $id) {
                $ids[$key] = $id;
            }
        }

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

    // -- Array normalizable

    /**
     * @param NormalizedIdList $data
     */
    public static function denormalize(array $data): static
    {
        $idClass = static::handlesIdClass();

        $ids = [];
        foreach ($data as $idString) {
            $ids[] = new $idClass($idString);
        }

        return new static($ids);
    }

    /**
     * @return NormalizedIdList
     */
    public function normalize(): array
    {
        return $this->idsAsStringList();
    }

    // -- Transformers

    /**
     * @param T $id
     */
    public function addId(Id $id): static
    {
        if ($this->containsId($id)) {
            throw new Exception\IdAlreadyInList($id);
        }

        $ids = $this->ids;
        $ids[] = $id;

        return new static($ids);
    }

    /**
     * @template TT of T
     *
     * @param static<TT> $idList
     *
     * @return static<TT>
     */
    public function addIds(self $idList): static
    {
        $this->mustContainNoneIds($idList);

        $newIds = $this->ids;
        foreach ($idList as $id) {
            $newIds[$id->value] = $id;
        }

        return new static($newIds);
    }

    /**
     * @param T $id
     */
    public function addIdWhenNotInList(Id $id): static
    {
        if ($this->containsId($id)) {
            return new static($this->ids);
        }

        $ids = $this->ids;
        $ids[$id->value] = $id;

        return new static($ids);
    }

    /**
     * @template TT of T
     *
     * @param static<TT> $idList
     *
     * @return static<TT>
     */
    public function addIdsWhenNotInList(self $idList): static
    {
        $newIds = $this->ids;
        foreach ($idList as $id) {
            if ($this->notContainsId($id)) {
                $newIds[$id->value] = $id;
            }
        }

        return new static($newIds);
    }

    /**
     * @param T $id
     */
    public function removeId(Id $id): static
    {
        $this->mustContainId($id);

        $idsWithoutIdToRemove = $this->ids;
        unset($idsWithoutIdToRemove[$id->value]);

        return new static($idsWithoutIdToRemove);
    }

    /**
     * @param static $idList
     */
    public function removeIds(self $idList): static
    {
        $this->mustContainEveryId($idList);

        $idsWithoutIdsToRemove = $this->ids;
        foreach ($idList as $id) {
            unset($idsWithoutIdsToRemove[$id->value]);
        }

        return new static($idsWithoutIdsToRemove);
    }

    /**
     * @param T $id
     */
    public function removeIdWhenInList(Id $id): static
    {
        if ($this->notContainsId($id)) {
            return new static($this->ids);
        }

        $idsWithoutIdToRemove = $this->ids;
        unset($idsWithoutIdToRemove[$id->value]);

        return new static($idsWithoutIdToRemove);
    }

    /**
     * @param static $idList
     */
    public function removeIdsWhenInList(self $idList): static
    {
        $idsWithoutIdToRemove = $this->ids;
        foreach ($idList as $id) {
            if (array_key_exists($id->value, $idsWithoutIdToRemove)) {
                unset($idsWithoutIdToRemove[$id->value]);
            }
        }

        return new static($idsWithoutIdToRemove);
    }

    /**
     * @param static $idList
     */
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
        $idsInList = [];
        foreach ($this->ids as $id) {
            if (array_key_exists($id->value, $idList->ids)) {
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

    /**
     * Psalm doesn't yet realize when a function is pure and when not. To prevent us from marking every single use by hand (which will
     * reduce the readability), we ignore the purity for now and will change the call here to pure-callable as soon as Psalm can handle
     * it.
     *
     * @template R
     *
     * @psalm-param callable(T):R $mapFunction
     *
     * @return array<string, R>
     */
    public function mapWithIdKeys(callable $mapFunction): array
    {
        $map = [];
        foreach ($this->ids as $idString => $id) {
            $map[$idString] = $mapFunction($id);
        }

        return $map;
    }

    /**
     * @psalm-param callable(T):bool $filterFunction
     */
    public function filter(callable $filterFunction): static
    {
        return new static(array_filter(
            $this->ids,
            $filterFunction,
        ));
    }

    /**
     * @psalm-param callable(T):bool $everyFunction
     */
    public function every(callable $everyFunction): bool
    {
        foreach ($this->ids as $id) {
            if ($everyFunction($id) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @psalm-param callable(T):bool $someFunction
     */
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

    /**
     * @param T $id
     */
    public function containsId(Id $id): bool
    {
        return array_key_exists($id->value, $this->ids);
    }

    /**
     * @param T $id
     */
    public function notContainsId(Id $id): bool
    {
        return !array_key_exists($id->value, $this->ids);
    }

    /**
     * @param static $idList
     */
    public function containsEveryId(self $idList): bool
    {
        foreach ($idList as $id) {
            if ($this->notContainsId($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param static $idList
     */
    public function notContainsEveryId(self $idList): bool
    {
        foreach ($idList as $id) {
            if ($this->notContainsId($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Opposite function is not called notContainsSomeIds, but containsNoneIds.
     *
     * @param static $idList
     */
    public function containsSomeIds(self $idList): bool
    {
        foreach ($idList as $id) {
            if ($this->containsId($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param static $idList
     */
    public function containsNoneIds(self $idList): bool
    {
        foreach ($idList as $id) {
            if ($this->containsId($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param static $idList
     */
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

    /**
     * @param static $idList
     */
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

    /**
     * @return list<string>
     */
    public function idsAsStringList(): array
    {
        return array_keys($this->ids);
    }

    // -- Guards

    /**
     * @param T                       $id
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListDoesNotContainId
     */
    public function mustContainId(
        Id $id,
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->notContainsId($id)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListDoesNotContainId($id);
        }
    }

    /**
     * @param T                       $id
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListDoesContainId
     */
    public function mustNotContainId(
        Id $id,
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->containsId($id)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListDoesContainId($id);
        }
    }

    /**
     * @param static                  $idList
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListDoesNotContainEveryId
     */
    public function mustContainEveryId(
        self $idList,
        ?callable $otherwiseThrow = null,
    ): void {
        if (!$this->containsEveryId($idList)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListDoesNotContainEveryId();
        }
    }

    /**
     * @param static                  $idList
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListDoesContainEveryId
     */
    public function mustNotContainEveryId(
        self $idList,
        ?callable $otherwiseThrow = null,
    ): void {
        if (!$this->notContainsEveryId($idList)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListDoesContainEveryId();
        }
    }

    /**
     * @param static                  $idList
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListDoesNotContainSomeIds
     */
    public function mustContainSomeIds(
        self $idList,
        ?callable $otherwiseThrow = null,
    ): void {
        if (!$this->containsSomeIds($idList)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListDoesNotContainSomeIds();
        }
    }

    /**
     * @param static                  $idList
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListDoesContainNoneIds
     */
    public function mustContainNoneIds(
        self $idList,
        ?callable $otherwiseThrow = null,
    ): void {
        if (!$this->containsNoneIds($idList)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListDoesContainNoneIds();
        }
    }

    /**
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListIsNotEmpty
     */
    public function mustBeEmpty(
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->isNotEmpty()) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListIsNotEmpty();
        }
    }

    /**
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListIsEmpty
     */
    public function mustNotBeEmpty(
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->isEmpty()) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListIsEmpty();
        }
    }

    /**
     * @param static                  $idList
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListsMustBeEqual
     */
    public function mustBeEqualTo(
        self $idList,
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->isNotEqualTo($idList)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListsMustBeEqual();
        }
    }

    /**
     * @param static                  $idList
     * @param ?callable(): \Throwable $otherwiseThrow
     *
     * @throws \Throwable
     * @throws Exception\IdListsMustNotBeEqual
     */
    public function mustNotBeEqualTo(
        self $idList,
        ?callable $otherwiseThrow = null,
    ): void {
        if ($this->isEqualTo($idList)) {
            throw $otherwiseThrow !== null
                ? $otherwiseThrow()
                : new Exception\IdListsMustNotBeEqual();
        }
    }

    /**
     * @template TT of T
     *
     * @param array<int, Id> $ids
     *
     * @psalm-param array<int, TT> $ids
     *
     * @throws Exception\IdClassNotHandledInList
     */
    private static function mustOnlyContainIdsOfHandledClass(array $ids): void
    {
        $idClass = static::handlesIdClass();
        foreach ($ids as $id) {
            if ($id::class !== $idClass) {
                throw new Exception\IdClassNotHandledInList(static::class, $id::class);
            }
        }
    }

    // -- Iterator aggregate

    /**
     * @return \Iterator<int, T>
     */
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

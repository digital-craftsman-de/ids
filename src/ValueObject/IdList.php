<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Exception\DuplicateIds;
use DigitalCraftsman\Ids\ValueObject\Exception\IdAlreadyInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesNotContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListIsNotEmpty;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListsMustBeEqual;

abstract class IdList implements \Iterator, \Countable
{
    /**
     * The id type has to be overwritten in the child class so that the correct id type is used in denormalization.
     *
     * @var array<int, BaseId>
     */
    public array $ids;

    public int $index;

    // Construction

    /** @param array<int, BaseId> $ids */
    final public function __construct(
        array $ids,
    ) {
        self::mustNotContainDuplicateIds($ids);

        $this->ids = array_values($ids);
        $this->index = 0;
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
    final public static function merge(array $idLists): static
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

    abstract public static function handlesIdClass(): string;

    // Mutation

    public function addId(BaseId $id): static
    {
        if (in_array($id, $this->ids, false)) {
            throw new IdAlreadyInList($id);
        }

        $ids = $this->ids;
        $ids[] = $id;

        return new static($ids);
    }

    public function removeId(BaseId $id): static
    {
        $ids = array_filter($this->ids, static function (BaseId $currentId) use ($id) {
            return $currentId->isNotEqualTo($id);
        });

        return new static($ids);
    }

    public function diff(self $idList): static
    {
        $idsNotInList = [];
        foreach ($this->ids as $id) {
            if ($idList->notContainsId($id)) {
                $idsNotInList[] = $id;
            }
        }

        return new static($idsNotInList);
    }

    /**
     * Returns an id list of ids which exist in both lists. The order of the new list is the same as the list on which the function is
     * triggered from. The supplied list is only used for validation and not for order.
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

    // Guards

    /** @throws IdListDoesNotContainId */
    public function mustContainId(BaseId $id): void
    {
        foreach ($this->ids as $existingId) {
            if ($existingId->isEqualTo($id)) {
                return;
            }
        }

        throw new IdListDoesNotContainId($id);
    }

    /** @throws IdListDoesContainId */
    public function mustNotContainId(BaseId $id): void
    {
        foreach ($this->ids as $existingId) {
            if ($existingId->isEqualTo($id)) {
                throw new IdListDoesContainId($id);
            }
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

    // Accessors

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
        return $this->diff($idList)->count() === 0;
    }

    public function isNotEqualTo(self $idList): bool
    {
        return !$this->isEqualTo($idList);
    }

    public function isEmpty(): bool
    {
        return count($this->ids) === 0;
    }

    public function isNotEmpty(): bool
    {
        return count($this->ids) > 0;
    }

    public function isInSameOrder(self $orderedList): bool
    {
        $orderedListWithIdenticalIds = $orderedList->intersect($this);
        foreach ($this->ids as $index => $id) {
            if (!$orderedListWithIdenticalIds->hasIdAtPosition($index)) {
                return false;
            }

            if ($orderedListWithIdenticalIds->idAtPosition($index)->isNotEqualTo($id)) {
                return false;
            }
        }

        return true;
    }

    public function hasIdAtPosition(int $position): bool
    {
        return array_key_exists($position, $this->ids);
    }

    public function idAtPosition(int $position): BaseId
    {
        return $this->ids[$position];
    }

    // Transformer

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

    // Getter

    /** @return array<int, string> */
    public function idsAsStringList(): array
    {
        $ids = [];
        foreach ($this->ids as $id) {
            $ids[] = (string) $id;
        }

        return $ids;
    }

    // Iterator

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

    // Countable

    public function count(): int
    {
        return count($this->ids);
    }
}

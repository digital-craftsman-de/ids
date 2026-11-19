<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\ValueObject\IdList;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Type;

/**
 * Connection to be used as `wrapper_class` of a DBAL connection. It allows to pass an id list directly as query parameter
 * (e.g. for `IN (:userIds)`), without calling `idsAsStringList()` and without adding `ArrayParameterType::STRING` as
 * parameter type.
 *
 * Every parameter that is an id list is converted to its list of id strings. The parameter type is set to
 * `ArrayParameterType::STRING` when there is none or when it's `ParameterType::STRING`, which the ORM infers for every object
 * passed to a native query. An explicit array parameter type (e.g. `ArrayParameterType::ASCII`) is kept. Any other explicit
 * type (e.g. the Doctrine type of the id list to compare a JSON column) leaves the parameter as it is, so that the type
 * converts it.
 *
 * All the convenience methods (`fetchOne`, `fetchAllAssociative`, `iterateNumeric`, …) and the native queries of the ORM end
 * up in `executeQuery`, so they are covered as well.
 *
 * The class isn't final, so an application that needs a wrapper class of its own can extend it.
 *
 * @api
 */
class IdListAwareConnection extends Connection
{
    /**
     * @param list<mixed>|array<string, mixed>                                        $params
     * @param array<int<0, max>|string, ArrayParameterType|ParameterType|Type|string> $types
     */
    #[\Override]
    public function executeQuery(
        string $sql,
        array $params = [],
        array $types = [],
        ?QueryCacheProfile $qcp = null,
    ): Result {
        [$params, $types] = self::convertIdListParameters($params, $types);

        return parent::executeQuery($sql, $params, $types, $qcp);
    }

    /**
     * @param list<mixed>|array<string, mixed>                                        $params
     * @param array<int<0, max>|string, ArrayParameterType|ParameterType|Type|string> $types
     */
    #[\Override]
    public function executeCacheQuery(
        string $sql,
        array $params,
        array $types,
        QueryCacheProfile $qcp,
    ): Result {
        [$params, $types] = self::convertIdListParameters($params, $types);

        return parent::executeCacheQuery($sql, $params, $types, $qcp);
    }

    /**
     * @param list<mixed>|array<string, mixed>                                        $params
     * @param array<int<0, max>|string, ArrayParameterType|ParameterType|Type|string> $types
     *
     * @return int|numeric-string
     */
    #[\Override]
    public function executeStatement(
        string $sql,
        array $params = [],
        array $types = [],
    ): int | string {
        [$params, $types] = self::convertIdListParameters($params, $types);

        return parent::executeStatement($sql, $params, $types);
    }

    /**
     * @param list<mixed>|array<string, mixed>                                        $params
     * @param array<int<0, max>|string, ArrayParameterType|ParameterType|Type|string> $types
     *
     * @return array{
     *   0: list<mixed>|array<string, mixed>,
     *   1: array<int<0, max>|string, ArrayParameterType|ParameterType|Type|string>,
     * }
     */
    private static function convertIdListParameters(
        array $params,
        array $types,
    ): array {
        /** @psalm-suppress MixedAssignment */
        foreach ($params as $key => $value) {
            if (!$value instanceof IdList) {
                continue;
            }

            $type = $types[$key] ?? null;
            if ($type === null
                || $type === ParameterType::STRING
            ) {
                $types[$key] = ArrayParameterType::STRING;
            } elseif (!$type instanceof ArrayParameterType) {
                // Another explicit type converts the id list itself.
                continue;
            }

            $params[$key] = $value->idsAsStringList();
        }

        return [$params, $types];
    }
}

<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\Doctrine;

use DigitalCraftsman\Ids\Test\Doctrine\UserIdListType;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

#[CoversClass(IdListAwareConnection::class)]
final class IdListAwareConnectionTest extends TestCase
{
    private IdListAwareConnection $connection;

    private UserId $idOfUserTom;
    private UserId $idOfUserJane;
    private UserId $idOfUserMax;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
            'wrapperClass' => IdListAwareConnection::class,
        ]);
        self::assertInstanceOf(IdListAwareConnection::class, $connection);
        $this->connection = $connection;

        $this->connection->executeStatement('CREATE TABLE user (user_id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL)');

        $this->idOfUserTom = UserId::generateRandom();
        $this->idOfUserJane = UserId::generateRandom();
        $this->idOfUserMax = UserId::generateRandom();

        foreach ([
            'Tom' => $this->idOfUserTom,
            'Jane' => $this->idOfUserJane,
            'Max' => $this->idOfUserMax,
        ] as $name => $userId) {
            $this->connection->executeStatement(
                'INSERT INTO user (user_id, name) VALUES (:userId, :name)',
                [
                    'userId' => $userId->value,
                    'name' => $name,
                ],
            );
        }
    }

    #[Test]
    public function execute_query_converts_id_list_without_type(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserTom,
            $this->idOfUserMax,
        ]);

        // -- Act
        $names = $this->connection->fetchFirstColumn(
            'SELECT name FROM user WHERE user_id IN (:userIds) ORDER BY name ASC',
            [
                'userIds' => $userIds,
            ],
        );

        // -- Assert
        self::assertSame(['Max', 'Tom'], $names);
    }

    #[Test]
    public function execute_query_converts_id_list_with_string_parameter_type(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserJane,
        ]);

        // -- Act
        $names = $this->connection->fetchFirstColumn(
            'SELECT name FROM user WHERE user_id IN (:userIds)',
            [
                'userIds' => $userIds,
            ],
            [
                'userIds' => ParameterType::STRING,
            ],
        );

        // -- Assert
        self::assertSame(['Jane'], $names);
    }

    #[Test]
    public function execute_query_keeps_explicit_array_parameter_type(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserTom,
            $this->idOfUserJane,
        ]);

        // -- Act
        $names = $this->connection->fetchFirstColumn(
            'SELECT name FROM user WHERE user_id IN (:userIds) ORDER BY name ASC',
            [
                'userIds' => $userIds,
            ],
            [
                'userIds' => ArrayParameterType::ASCII,
            ],
        );

        // -- Assert
        self::assertSame(['Jane', 'Tom'], $names);
    }

    #[Test]
    public function execute_query_leaves_id_list_with_other_explicit_type_to_the_type(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserTom,
        ]);

        // -- Act
        $value = $this->connection->fetchOne(
            'SELECT :userIds',
            [
                'userIds' => $userIds,
            ],
            [
                'userIds' => new UserIdListType(),
            ],
        );

        // -- Assert
        self::assertSame(json_encode([$this->idOfUserTom->value]), $value);
    }

    #[Test]
    public function execute_query_converts_id_list_after_id_list_with_other_explicit_type(): void
    {
        // -- Arrange
        $userIdsForType = new UserIdList([
            $this->idOfUserTom,
        ]);
        $userIds = new UserIdList([
            $this->idOfUserJane,
        ]);

        // -- Act
        $row = $this->connection->fetchNumeric(
            'SELECT :userIdsForType, (SELECT name FROM user WHERE user_id IN (:userIds))',
            [
                'userIdsForType' => $userIdsForType,
                'userIds' => $userIds,
            ],
            [
                'userIdsForType' => new UserIdListType(),
            ],
        );

        // -- Assert
        self::assertSame([json_encode([$this->idOfUserTom->value]), 'Jane'], $row);
    }

    #[Test]
    public function execute_query_converts_positional_id_list(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserJane,
            $this->idOfUserMax,
        ]);

        // -- Act
        $names = $this->connection->fetchFirstColumn(
            'SELECT name FROM user WHERE name <> ? AND user_id IN (?) ORDER BY name ASC',
            [
                'Max',
                $userIds,
            ],
        );

        // -- Assert
        self::assertSame(['Jane'], $names);
    }

    #[Test]
    public function execute_query_leaves_other_parameters_untouched(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserTom,
            $this->idOfUserJane,
        ]);

        // -- Act
        $names = $this->connection->fetchFirstColumn(
            'SELECT name FROM user WHERE user_id IN (:userIds) AND name = :name',
            [
                'userIds' => $userIds,
                'name' => 'Jane',
            ],
            [
                'name' => ParameterType::STRING,
            ],
        );

        // -- Assert
        self::assertSame(['Jane'], $names);
    }

    #[Test]
    public function execute_query_works_with_empty_id_list(): void
    {
        // -- Arrange
        $userIds = UserIdList::emptyList();

        // -- Act
        $names = $this->connection->fetchFirstColumn(
            'SELECT name FROM user WHERE user_id IN (:userIds)',
            [
                'userIds' => $userIds,
            ],
        );

        // -- Assert
        self::assertSame([], $names);
    }

    #[Test]
    public function execute_cache_query_converts_id_list(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserTom,
        ]);
        $resultCache = new ArrayAdapter();
        $queryCacheProfile = new QueryCacheProfile(0, 'users', $resultCache);

        // -- Act
        $names = $this->connection->executeCacheQuery(
            'SELECT name FROM user WHERE user_id IN (:userIds)',
            [
                'userIds' => $userIds,
            ],
            [],
            $queryCacheProfile,
        )->fetchFirstColumn();

        // -- Assert
        self::assertSame(['Tom'], $names);
        self::assertTrue($resultCache->hasItem('users'));
    }

    #[Test]
    public function execute_query_with_query_cache_profile_converts_id_list(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserJane,
        ]);
        $queryCacheProfile = new QueryCacheProfile(0, 'users', new ArrayAdapter());

        // -- Act
        $names = $this->connection->executeQuery(
            'SELECT name FROM user WHERE user_id IN (:userIds)',
            [
                'userIds' => $userIds,
            ],
            [],
            $queryCacheProfile,
        )->fetchFirstColumn();

        // -- Assert
        self::assertSame(['Jane'], $names);
    }

    #[Test]
    public function execute_statement_converts_id_list(): void
    {
        // -- Arrange
        $userIds = new UserIdList([
            $this->idOfUserTom,
            $this->idOfUserJane,
        ]);

        // -- Act
        $affectedRows = $this->connection->executeStatement(
            'DELETE FROM user WHERE user_id IN (:userIds)',
            [
                'userIds' => $userIds,
            ],
        );

        // -- Assert
        self::assertSame(2, $affectedRows);
        self::assertSame(['Max'], $this->connection->fetchFirstColumn('SELECT name FROM user'));
    }
}

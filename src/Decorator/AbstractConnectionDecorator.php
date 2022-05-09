<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Decorator;

use Closure;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Types\Type;

abstract class AbstractConnectionDecorator extends Connection
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        parent::__construct(
            $connection->getParams(),
            $connection->getDriver(),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );
    }

    public function prepare(string $sql): Statement
    {
        return $this->connection->prepare($sql);
    }

    public function query(string $sql): Result
    {
        return $this->connection->query($sql);
    }

    public function quote($value, $type = ParameterType::STRING)
    {
        return $this->connection->quote($value, $type);
    }

    public function exec(string $sql): int
    {
        return $this->connection->exec($sql);
    }

    public function lastInsertId($name = null)
    {
        return $this->connection->lastInsertId($name);
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    public function getParams(): array
    {
        return $this->connection->getParams();
    }

    public function getDatabase(): ?string
    {
        return $this->connection->getDatabase();
    }

    public function getDriver(): Driver
    {
        return $this->connection->getDriver();
    }

    public function getConfiguration(): Configuration
    {
        return $this->connection->getConfiguration();
    }

    public function getEventManager(): EventManager
    {
        return $this->connection->getEventManager();
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->connection->getDatabasePlatform();
    }

    public function getExpressionBuilder(): ExpressionBuilder
    {
        return $this->connection->getExpressionBuilder();
    }

    public function createExpressionBuilder(): ExpressionBuilder
    {
        return $this->connection->createExpressionBuilder();
    }

    public function connect(): bool
    {
        return $this->connection->connect();
    }

    public function isAutoCommit(): bool
    {
        return $this->connection->isAutoCommit();
    }

    public function setAutoCommit($autoCommit): void
    {
        $this->connection->setAutoCommit($autoCommit);
    }

    public function fetchAssociative(string $query, array $params = [], array $types = [])
    {
        return $this->connection->fetchAssociative($query, $params, $types);
    }

    public function fetchNumeric(string $query, array $params = [], array $types = [])
    {
        return $this->connection->fetchNumeric($query, $params, $types);
    }

    public function fetchOne(string $query, array $params = [], array $types = [])
    {
        return $this->connection->fetchOne($query, $params, $types);
    }

    public function fetchAllNumeric(string $query, array $params = [], array $types = []): array
    {
        return $this->connection->fetchAllNumeric($query, $params, $types);
    }

    public function fetchAllAssociative(string $query, array $params = [], array $types = []): array
    {
        return $this->connection->fetchAllAssociative($query, $params, $types);
    }

    public function fetchAllKeyValue(string $query, array $params = [], array $types = []): array
    {
        return $this->connection->fetchAllKeyValue($query, $params, $types);
    }

    public function fetchAllAssociativeIndexed(string $query, array $params = [], array $types = []): array
    {
        return $this->connection->fetchAllAssociativeIndexed($query, $params, $types);
    }

    public function fetchFirstColumn(string $query, array $params = [], array $types = []): array
    {
        return $this->connection->fetchFirstColumn($query, $params, $types);
    }

    public function iterateNumeric(string $query, array $params = [], array $types = []): \Traversable
    {
        return $this->connection->iterateNumeric($query, $params, $types);
    }

    public function iterateAssociative(string $query, array $params = [], array $types = []): \Traversable
    {
        return $this->connection->iterateAssociative($query, $params, $types);
    }

    public function iterateKeyValue(string $query, array $params = [], array $types = []): \Traversable
    {
        return $this->connection->iterateKeyValue($query, $params, $types);
    }

    public function iterateAssociativeIndexed(string $query, array $params = [], array $types = []): \Traversable
    {
        return $this->connection->iterateAssociativeIndexed($query, $params, $types);
    }

    public function iterateColumn(string $query, array $params = [], array $types = []): \Traversable
    {
        return $this->connection->iterateColumn($query, $params, $types);
    }

    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }

    public function isTransactionActive(): bool
    {
        return $this->connection->isTransactionActive();
    }

    public function delete($table, array $criteria, array $types = [])
    {
        return $this->connection->delete($table, $criteria, $types);
    }

    public function close(): void
    {
        $this->connection->close();
    }

    public function setTransactionIsolation($level)
    {
        $this->connection->setTransactionIsolation($level);
    }

    public function getTransactionIsolation(): int
    {
        return $this->connection->getTransactionIsolation();
    }

    public function update($table, array $data, array $criteria, array $types = [])
    {
        return $this->connection->update($table, $data, $criteria, $types);
    }

    public function insert($table, array $data, array $types = [])
    {
        return $this->connection->insert($table, $data, $types);
    }

    public function quoteIdentifier($str): string
    {
        return $this->connection->quoteIdentifier($str);
    }

    public function executeQuery(string $sql, array $params = [], $types = [], QueryCacheProfile $qcp = null): Result
    {
        return $this->connection->executeQuery($sql, $params, $types, $qcp);
    }

    public function executeCacheQuery($sql, $params, $types, QueryCacheProfile $qcp): Result
    {
        return $this->connection->executeCacheQuery($sql, $params, $types, $qcp);
    }

    public function executeStatement($sql, array $params = [], array $types = [])
    {
        return $this->connection->executeStatement($sql, $params, $types);
    }

    public function executeUpdate(string $sql, array $params = [], array $types = []): int
    {
        return $this->connection->executeUpdate($sql, $params, $types);
    }

    public function getTransactionNestingLevel(): int
    {
        return $this->connection->getTransactionNestingLevel();
    }

    public function transactional(Closure $func)
    {
        return $this->connection->transactional($func);
    }

    public function setNestTransactionsWithSavepoints($nestTransactionsWithSavepoints): void
    {
        $this->connection->setNestTransactionsWithSavepoints($nestTransactionsWithSavepoints);
    }

    public function getNestTransactionsWithSavepoints(): bool
    {
        return $this->connection->getNestTransactionsWithSavepoints();
    }

    public function createSavepoint($savepoint): void
    {
        $this->connection->createSavepoint($savepoint);
    }

    public function releaseSavepoint($savepoint): void
    {
        $this->connection->releaseSavepoint($savepoint);
    }

    public function rollbackSavepoint($savepoint): void
    {
        $this->connection->rollbackSavepoint($savepoint);
    }

    public function getWrappedConnection(): DriverConnection
    {
        return $this->connection->getWrappedConnection();
    }

    public function getNativeConnection()
    {
        return $this->connection->getNativeConnection();
    }

    public function getSchemaManager(): AbstractSchemaManager
    {
        return $this->connection->getSchemaManager();
    }

    public function createSchemaManager(): AbstractSchemaManager
    {
        return $this->connection->createSchemaManager();
    }

    public function setRollbackOnly(): void
    {
        $this->connection->setRollbackOnly();
    }

    public function isRollbackOnly(): bool
    {
        return $this->connection->isRollbackOnly();
    }

    public function convertToDatabaseValue($value, $type)
    {
        return $this->connection->convertToDatabaseValue($value, $type);
    }

    public function convertToPHPValue($value, $type)
    {
        return $this->connection->convertToPHPValue($value, $type);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder();
    }
}

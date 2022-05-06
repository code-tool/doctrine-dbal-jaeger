<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Decorator;

use Closure;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;

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

    public function errorCode()
    {
        return $this->connection->errorCode();
    }

    public function errorInfo()
    {
        return $this->connection->errorInfo();
    }

    public function getParams()
    {
        return $this->connection->getParams();
    }

    public function getDatabase()
    {
        return $this->connection->getDatabase();
    }

    public function getHost()
    {
        return $this->connection->getHost();
    }

    public function getPort()
    {
        return $this->connection->getPort();
    }

    public function getUsername()
    {
        return $this->connection->getUsername();
    }

    public function getPassword()
    {
        return $this->connection->getPassword();
    }

    public function getDriver()
    {
        return $this->connection->getDriver();
    }

    public function getConfiguration()
    {
        return $this->connection->getConfiguration();
    }

    public function getEventManager()
    {
        return $this->connection->getEventManager();
    }

    public function getDatabasePlatform()
    {
        return $this->connection->getDatabasePlatform();
    }

    public function getExpressionBuilder()
    {
        return $this->connection->getExpressionBuilder();
    }

    public function connect(): bool
    {
        return $this->connection->connect();
    }

    public function isAutoCommit()
    {
        return $this->connection->isAutoCommit();
    }

    public function setAutoCommit($autoCommit)
    {
        $this->connection->setAutoCommit($autoCommit);
    }

    public function setFetchMode($fetchMode)
    {
        $this->connection->setFetchMode($fetchMode);
    }

    public function fetchAssoc($statement, array $params = [], array $types = [])
    {
        return $this->connection->fetchAssoc($statement, $params, $types);
    }

    public function fetchArray($statement, array $params = [], array $types = [])
    {
        return $this->connection->fetchArray($statement, $params, $types);
    }

    public function fetchColumn($statement, array $params = [], $column = 0, array $types = [])
    {
        return $this->connection->fetchColumn($statement, $params, $column, $types);
    }

    public function isConnected()
    {
        return $this->connection->isConnected();
    }

    public function isTransactionActive()
    {
        return $this->connection->isTransactionActive();
    }

    public function delete($tableExpression, array $identifier, array $types = [])
    {
        return $this->connection->delete($tableExpression, $identifier, $types);
    }

    public function close()
    {
        $this->connection->close();
    }

    public function setTransactionIsolation($level)
    {
        $this->connection->setTransactionIsolation($level);
    }

    public function getTransactionIsolation()
    {
        return $this->connection->getTransactionIsolation();
    }

    public function update($tableExpression, array $data, array $identifier, array $types = [])
    {
        return $this->connection->update($tableExpression, $data, $identifier, $types);
    }

    public function insert($tableExpression, array $data, array $types = [])
    {
        return $this->connection->insert($tableExpression, $data, $types);
    }

    public function quoteIdentifier($str)
    {
        return $this->connection->quoteIdentifier($str);
    }

    public function fetchAll($sql, array $params = [], $types = [])
    {
        return $this->connection->fetchAll($sql, $params, $types);
    }

    public function executeQuery(string $sql, array $params = [], $types = [], QueryCacheProfile $qcp = null): Result
    {
        return $this->connection->executeQuery($sql, $params, $types, $qcp);
    }

    public function executeCacheQuery($query, $params, $types, QueryCacheProfile $qcp)
    {
        return $this->connection->executeCacheQuery($query, $params, $types, $qcp);
    }

    public function project($query, array $params, Closure $function)
    {
        return $this->connection->project($query, $params, $function);
    }

    public function executeUpdate(string $sql, array $params = [], array $types = []): int
    {
        return $this->connection->executeUpdate($sql, $params, $types);
    }

    public function getTransactionNestingLevel()
    {
        return $this->connection->getTransactionNestingLevel();
    }

    public function transactional(Closure $func)
    {
        return $this->connection->transactional($func);
    }

    public function setNestTransactionsWithSavepoints($nestTransactionsWithSavepoints)
    {
        $this->connection->setNestTransactionsWithSavepoints($nestTransactionsWithSavepoints);
    }

    public function getNestTransactionsWithSavepoints()
    {
        return $this->connection->getNestTransactionsWithSavepoints();
    }

    public function createSavepoint($savepoint)
    {
        $this->connection->createSavepoint($savepoint);
    }

    public function releaseSavepoint($savepoint)
    {
        $this->connection->releaseSavepoint($savepoint);
    }

    public function rollbackSavepoint($savepoint)
    {
        $this->connection->rollbackSavepoint($savepoint);
    }

    public function getWrappedConnection()
    {
        return $this->connection->getWrappedConnection();
    }

    public function getSchemaManager()
    {
        return $this->connection->getSchemaManager();
    }

    public function setRollbackOnly()
    {
        $this->connection->setRollbackOnly();
    }

    public function isRollbackOnly()
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

    public function resolveParams(array $params, array $types)
    {
        return $this->connection->resolveParams($params, $types);
    }

    public function createQueryBuilder()
    {
        return new QueryBuilder($this);
    }

    public function ping()
    {
        return $this->connection->ping();
    }
}

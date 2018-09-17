<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Decorator;

use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\ParameterType;

class AbstractConnectionDecorator implements DriverConnection
{
    private $connection;

    public function __construct(DriverConnection $connection)
    {
        $this->connection = $connection;
    }

    public function prepare($prepareString)
    {
        return $this->connection->prepare($prepareString);
    }

    public function query()
    {
        return $this->connection->query();
    }

    public function quote($input, $type = ParameterType::STRING)
    {
        return $this->connection->quote($input, $type);
    }

    public function exec($statement)
    {
        return $this->connection->exec($statement);
    }

    public function lastInsertId($name = null)
    {
        return $this->connection->lastInsertId($name);
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollBack()
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
}

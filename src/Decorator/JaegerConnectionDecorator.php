<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Decorator;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Jaeger\Tag\DbalAutoCommitTag;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Doctrine\DBAL\Jaeger\Tag\DbalNestingLevelTag;
use Doctrine\DBAL\Jaeger\Tag\DbalRowNumberTag;
use Jaeger\Tag\DbInstanceTag;
use Jaeger\Tag\DbStatementTag;
use Jaeger\Tag\DbType;
use Jaeger\Tag\DbUser;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerConnectionDecorator extends AbstractConnectionDecorator
{
    private $tracer;

    /**
     * @var int|null
     */
    private $maxSqlLength;

    public function __construct(Connection $connection, TracerInterface $tracer, ?int $maxSqlLength = null)
    {
        $this->tracer = $tracer;
        $this->maxSqlLength = $maxSqlLength;
        parent::__construct($connection);
    }

    public function connect(): bool
    {
        if ($this->isConnected()) {
            return false;
        }
        $span = $this->tracer
            ->start('dbal.connect')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::connect();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->addTag(new DbType($this->getDatabasePlatform()->getName()))->finish();
        }
    }

    public function prepare($prepareString)
    {
        $span = $this->tracer
            ->start('dbal.prepare')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbStatementTag($this->cutLongSql($prepareString)))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::prepare($prepareString);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null)
    {
        $span = $this->tracer
            ->start('dbal.execute')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbStatementTag($this->cutLongSql($query)))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::executeQuery($query, $params, $types, $qcp);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function executeUpdate($query, array $params = [], array $types = [])
    {
        $span = $this->tracer
            ->start('dbal.execute')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbStatementTag($this->cutLongSql($query)))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::executeUpdate($query, $params, $types);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function query()
    {
        $args = func_get_args();
        $span = $this->tracer
            ->start('dbal.query')
            ->addTag(new DbStatementTag($this->cutLongSql($args[0])))
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::query(...$args);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function exec($statement)
    {
        $span = $this->tracer
            ->start('dbal.exec')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            $rows = parent::exec($statement);
            $span->addTag(new DbalRowNumberTag($rows));

            return $rows;
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function beginTransaction()
    {
        $span = $this->tracer
            ->start('dbal.transaction')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()));
        try {
            parent::beginTransaction();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))->finish();
        }
    }

    public function commit()
    {
        $span = $this->tracer
            ->start('dbal.commit')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()));
        try {
            parent::commit();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))->finish();
        }
    }

    public function rollBack()
    {
        $span = $this->tracer
            ->start('dbal.rollback')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()));
        try {
            return parent::rollBack();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))->finish();
        }
    }

    private function cutLongSql(string $string): string
    {
        if (null === $this->maxSqlLength) {
            return $string;
        }

        return substr($string, 0, $this->maxSqlLength);
    }
}

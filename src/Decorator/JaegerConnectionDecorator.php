<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Decorator;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Jaeger\Tag\DbalAutoCommitTag;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Doctrine\DBAL\Jaeger\Tag\DbalNestingLevelTag;
use Doctrine\DBAL\Jaeger\Tag\DbalRowNumberTag;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
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
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::connect();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span
                ->addTag(new DbType(get_class($this->getDatabasePlatform())))
                ->finish();
        }
    }

    public function prepare(string $sql): Statement
    {
        $span = $this->tracer
            ->start('dbal.prepare')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbStatementTag($this->cutLongSql($sql)))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::prepare($sql);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function executeQuery(string $sql, array $params = [], $types = [], QueryCacheProfile $qcp = null): Result
    {
        $span = $this->tracer
            ->start('dbal.execute')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbStatementTag($this->cutLongSql($sql)))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::executeQuery($sql, $params, $types, $qcp);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function executeStatement($sql, array $params = [], array $types = [])
    {
        $span = $this->tracer
            ->start('dbal.execute')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbStatementTag($this->cutLongSql($sql)))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::executeStatement($sql, $params, $types);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->finish();
        }
    }

    public function beginTransaction(): bool
    {
        $span = $this->tracer
            ->start('dbal.transaction')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()));
        try {
            return parent::beginTransaction();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))->finish();
        }
    }

    public function commit(): bool
    {
        $span = $this->tracer
            ->start('dbal.commit')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()));
        try {
            return parent::commit();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))->finish();
        }
    }

    public function rollBack(): bool
    {
        $span = $this->tracer
            ->start('dbal.rollback')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
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

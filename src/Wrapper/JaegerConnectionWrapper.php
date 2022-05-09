<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
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

class JaegerConnectionWrapper extends Connection
{
    /**
     * @var TracerInterface $tracer
     */
    private $tracer;

    /**
     * @var int|null
     */
    private $maxSqlLength = null;

    public function setTracer(TracerInterface $tracer): self
    {
        $this->tracer = $tracer;

        return $this;
    }

    public function setMaxSqlLength(?int $maxSqlLength): self
    {
        $this->maxSqlLength = $maxSqlLength;

        return $this;
    }

    public function connect(): bool
    {
        if ($this->isConnected()) {
            return false;
        }
        $span = $this->tracer->start('dbal.connect');
        try {
            return parent::connect();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            if ($this->isConnected()) {
                $span
                    ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
                    ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))
                    ->addTag(new DbType(get_class($this->getDatabasePlatform())));
            }
            $this->tracer->finish($span);
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
            return $this->wrappedPrepare($sql);
        } catch (\Exception $e) {
            $span
                ->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());

            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }

    private function wrappedPrepare($sql): JaegerStatementWrapper
    {
        $connection = $this->getWrappedConnection();

        try {
            $statement = $connection->prepare($sql);
        } catch (Exception $e) {
            throw $this->convertExceptionDuringQuery($e, $sql);
        }

        $stmtWrapper = new JaegerStatementWrapper($this, $statement, $sql);

        $stmtWrapper->setTracer($this->tracer);
        $stmtWrapper->setMaxSqlLength($this->maxSqlLength);

        return $stmtWrapper;
    }

    public function executeQuery(string $sql, array $params = [], $types = [], ?QueryCacheProfile $qcp = null): Result
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
            $this->tracer->finish($span);
        }
    }

    public function executeUpdate(string $sql, array $params = [], array $types = []): int
    {
        $span = $this->tracer
            ->start('dbal.execute')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbStatementTag($this->cutLongSql($sql)))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::executeUpdate($sql, $params, $types);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function query(string $sql): Result
    {
        $span = $this->tracer
            ->start('dbal.query')
            ->addTag(new DbStatementTag($this->cutLongSql($sql)))
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::query($sql);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function exec(string $sql): int
    {
        $span = $this->tracer
            ->start('dbal.exec')
            ->addTag(new DbType(get_class($this->getDatabasePlatform())))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            $rows = parent::exec($sql);
            $span->addTag(new DbalRowNumberTag($rows));

            return $rows;
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
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
            $this->tracer->finish(
                $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))
            );
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
            $this->tracer->finish(
                $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))
            );
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
            $this->tracer->finish(
                $span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()))
            );
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

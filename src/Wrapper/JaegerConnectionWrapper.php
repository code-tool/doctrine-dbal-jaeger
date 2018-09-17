<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

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

class JaegerConnectionWrapper extends Connection
{
    /**
     * @var TracerInterface $tracer
     */
    private $tracer;

    public function setTracer(TracerInterface $tracer)
    {
        $this->tracer = $tracer;

        return $this;
    }

    public function connect()
    {
        $span = $this->tracer
            ->start('dbal.connect')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            parent::connect();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span->addTag(new DbType($this->getDatabasePlatform()->getName())));
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
            ->addTag(new DbStatementTag($prepareString))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::prepare($prepareString);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
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
            ->addTag(new DbStatementTag($query))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::executeQuery($query, $params, $types, $qcp);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
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
            ->addTag(new DbStatementTag($query))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::executeUpdate($query, $params, $types);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function query()
    {
        $span = $this->tracer
            ->start('dbal.query')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType($this->getDatabasePlatform()->getName()))
            ->addTag(new DbalAutoCommitTag($this->isAutoCommit()))
            ->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel()));
        try {
            return parent::query();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
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
            $this->tracer->finish($span);
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
            $this->tracer->finish($span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel())));
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
            $this->tracer->finish($span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel())));
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
            $this->tracer->finish($span->addTag(new DbalNestingLevelTag($this->getTransactionNestingLevel())));
        }
    }
}

<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Doctrine\DBAL\Jaeger\Tag\DbalRowNumberTag;
use Jaeger\Tag\DbInstanceTag;
use Jaeger\Tag\DbStatementTag;
use Jaeger\Tag\DbType;
use Jaeger\Tag\DbUser;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerConnectionWrapper extends Connection
{
    private $tracer;

    public function __construct(
        TracerInterface $tracer,
        array $params,
        Driver $driver,
        Configuration $config = null,
        EventManager $eventManager = null

    ) {
        $this->tracer = $tracer;
        parent::__construct($params, $driver, $config, $eventManager);
    }

    public function prepare($prepareString)
    {
        $span = $this->tracer
            ->start('dbal.prepare')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType('sql'))
            ->addTag(new DbStatementTag($prepareString));
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
            ->addTag(new DbType('sql'))
            ->addTag(new DbStatementTag($query));
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
            ->addTag(new DbType('sql'))
            ->addTag(new DbStatementTag($query));
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
            ->addTag(new DbType('sql'));
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
            ->addTag(new DbType('sql'));
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
            ->addTag(new DbType('sql'));
        try {
            parent::beginTransaction();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function commit()
    {
        $span = $this->tracer
            ->start('dbal.commit')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType('sql'));
        try {
            parent::commit();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function rollBack()
    {
        $span = $this->tracer
            ->start('dbal.rollback')
            ->addTag(new DbInstanceTag($this->getDatabase()))
            ->addTag(new DbUser($this->getUsername()))
            ->addTag(new DbType('sql'));
        try {
            return parent::rollBack();
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());
            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }
}

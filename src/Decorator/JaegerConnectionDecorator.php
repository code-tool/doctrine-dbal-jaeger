<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Decorator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Doctrine\DBAL\Jaeger\Tag\DbalRowNumberTag;
use Jaeger\Tag\DbInstanceTag;
use Jaeger\Tag\DbStatementTag;
use Jaeger\Tag\DbType;
use Jaeger\Tag\DbUser;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerConnectionDecorator extends AbstractConnectionDecorator
{
    /**
     * @var TracerInterface
     */
    private $tracer;

    public function __construct(Connection $connection, TracerInterface $tracer)
    {
        $this->tracer = $tracer;
        parent::__construct($connection);
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

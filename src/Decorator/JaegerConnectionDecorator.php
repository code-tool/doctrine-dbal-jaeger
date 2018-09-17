<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Decorator;

use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Doctrine\DBAL\Jaeger\Tag\DbalRowNumberTag;
use Jaeger\Tag\DbStatementTag;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerConnectionDecorator extends AbstractConnectionDecorator
{
    private $tracer;

    public function __construct(DriverConnection $connection, TracerInterface $tracer)
    {
        $this->tracer = $tracer;
        parent::__construct($connection);
    }

    public function prepare($prepareString)
    {
        $span = $this->tracer
            ->start('dbal.prepare')
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
        $span = $this->tracer->start('dbal.query');
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
        $span = $this->tracer->start('dbal.exec');
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
        $span = $this->tracer->start('dbal.transaction');
        try {
            return parent::beginTransaction();
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
        $span = $this->tracer->start('dbal.commit');
        try {
            return parent::commit();
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
        $span = $this->tracer->start('dbal.rollback');
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

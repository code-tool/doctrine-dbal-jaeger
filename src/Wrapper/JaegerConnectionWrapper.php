<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Jaeger\Tag\DbStatementTag;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerConnectionWrapper extends AbstractConnectionMiddleware
{
    private TracerInterface $tracer;

    private ?int $maxSqlLength;

    public function __construct(Connection $wrappedConnection, TracerInterface $tracer, ?int $maxSqlLength = null)
    {
        parent::__construct($wrappedConnection);

        $this->tracer = $tracer;
        $this->maxSqlLength = $maxSqlLength;
    }

    public function prepare(string $sql): Statement
    {
        $cutSql = $this->cutLongSql($sql);

        $span = $this->tracer
            ->start('dbal.prepare')
            ->addTag(new DbStatementTag($cutSql));

        try {
            return new JaegerStatementWrapper(
                parent::prepare($sql),
                $this->tracer,
                $sql
            );
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function query(string $sql): Result
    {
        $span = $this->tracer
            ->start('dbal.query')
            ->addTag(new DbStatementTag($this->cutLongSql($sql)));

        try {
            return parent::query($sql);
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function exec(string $sql): int
    {
        $span = $this->tracer
            ->start('dbal.exec')
            ->addTag(new DbStatementTag($this->cutLongSql($sql)));

        try {
            return parent::exec($sql);
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function beginTransaction(): bool
    {
        $span = $this->tracer
            ->start('dbal.transaction');

        try {
            return parent::beginTransaction();
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function commit(): bool
    {
        $span = $this->tracer
            ->start('dbal.commit');

        try {
            return parent::commit();
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function rollBack(): bool
    {
        $span = $this->tracer
            ->start('dbal.rollback');

        try {
            return parent::rollBack();
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    private function cutLongSql(string $string): string
    {
        if (null === $this->maxSqlLength) {
            return $string;
        }

        return \substr($string, 0, $this->maxSqlLength);
    }
}

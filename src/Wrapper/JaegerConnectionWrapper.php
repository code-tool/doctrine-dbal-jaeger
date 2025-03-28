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
    public function __construct(
        Connection $wrappedConnection,
        private readonly TracerInterface $tracer,
        private readonly ?int $maxSqlLength = null
    ) {
        parent::__construct($wrappedConnection);
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

    public function beginTransaction(): void
    {
        $span = $this->tracer
            ->start('dbal.transaction');

        try {
            parent::beginTransaction();
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function commit(): void
    {
        $span = $this->tracer
            ->start('dbal.commit');

        try {
            parent::commit();
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }

    public function rollBack(): void
    {
        $span = $this->tracer
            ->start('dbal.rollback');

        try {
            parent::rollBack();
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

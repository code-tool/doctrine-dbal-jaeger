<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Jaeger\Tag\DbStatementTag;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerStatementWrapper extends AbstractStatementMiddleware
{
    private TracerInterface $tracer;

    private string $sql;

    public function __construct(Statement $wrappedStatement, TracerInterface $tracer, string $sql)
    {
        parent::__construct($wrappedStatement);

        $this->tracer = $tracer;
        $this->sql = $sql;
    }

    public function execute($params = null): Result
    {
        $span = $this->tracer
            ->start('dbal.stmt.execute')
            ->addTag(new DbStatementTag($this->sql));

        try {
            return parent::execute($params);
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $this->tracer->finish($span);
        }
    }
}

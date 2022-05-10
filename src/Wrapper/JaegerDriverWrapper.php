<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Jaeger\Tag\DbType;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerDriverWrapper extends AbstractDriverMiddleware
{
    private TracerInterface $tracer;

    private ?int $maxSqlLength;

    public function __construct(Driver $wrappedDriver, TracerInterface $tracer, ?int $maxSqlLength = null)
    {
        parent::__construct($wrappedDriver);

        $this->tracer = $tracer;
        $this->maxSqlLength = $maxSqlLength;
    }

    public function connect(array $params): DriverConnection
    {
        $span = $this->tracer->start('dbal.driver.connect');

        try {
            return new JaegerConnectionWrapper(
                parent::connect($params),
                $this->tracer,
                $this->maxSqlLength
            );
        } catch (\Throwable $t) {
            $span
                ->addTag(new DbalErrorCodeTag($t->getCode()))
                ->addTag(new ErrorTag());

            throw $t;
        } finally {
            $span->addTag(new DbType(\get_class($this->getDatabasePlatform())));

            $this->tracer->finish($span);
        }
    }
}

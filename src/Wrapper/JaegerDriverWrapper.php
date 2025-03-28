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
    public function __construct(
        private readonly Driver $wrappedDriver,
        private readonly TracerInterface $tracer,
        private readonly ?int $maxSqlLength = null
    ) {
        parent::__construct($wrappedDriver);
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
            $span->addTag(new DbType($this->wrappedDriver::class));

            $this->tracer->finish($span);
        }
    }
}

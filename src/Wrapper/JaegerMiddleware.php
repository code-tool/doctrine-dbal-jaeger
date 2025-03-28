<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Jaeger\Tracer\TracerInterface;

class JaegerMiddleware implements Middleware
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly ?int $maxSqlLength = null
    ) {
    }

    public function wrap(Driver $driver): Driver
    {
        return new JaegerDriverWrapper($driver, $this->tracer, $this->maxSqlLength);
    }
}

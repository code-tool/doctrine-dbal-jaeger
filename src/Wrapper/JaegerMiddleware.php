<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Jaeger\Tracer\TracerInterface;

class JaegerMiddleware implements Middleware
{
    private TracerInterface $tracer;

    private ?int $maxSqlLength;

    public function __construct(TracerInterface $tracer, ?int $maxSqlLength = null)
    {
        $this->tracer = $tracer;
        $this->maxSqlLength = $maxSqlLength;
    }

    public function wrap(Driver $driver): Driver
    {
        return new JaegerDriverWrapper($driver, $this->tracer, $this->maxSqlLength);
    }
}

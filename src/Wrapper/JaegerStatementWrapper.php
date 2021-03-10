<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Wrapper;

use Doctrine\DBAL\Jaeger\Tag\DbalErrorCodeTag;
use Doctrine\DBAL\Statement;
use Jaeger\Tag\DbStatementTag;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;

class JaegerStatementWrapper extends Statement
{
    /**
     * @var TracerInterface $tracer
     */
    private $tracer;

    public function setTracer(TracerInterface $tracer)
    {
        $this->tracer = $tracer;

        return $this;
    }

    public function execute($params = null)
    {
        $span = $this->tracer
            ->start('dbal.prepare.execute')
            ->addTag(new DbStatementTag($this->sql));

        try {
            return parent::execute($params);
        } catch (\Exception $e) {
            $span->addTag(new DbalErrorCodeTag($e->getCode()))
                ->addTag(new ErrorTag());

            throw $e;
        } finally {
            $this->tracer->finish($span);
        }
    }
}

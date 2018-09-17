<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Tag;

use Jaeger\Tag\LongTag;

class DbalRowNumberTag extends LongTag
{
    public function __construct(int $value)
    {
        parent::__construct('dbal.rows', $value);
    }
}

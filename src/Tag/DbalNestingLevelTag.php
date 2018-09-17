<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Tag;

use Jaeger\Tag\LongTag;

class DbalNestingLevelTag extends LongTag
{
    public function __construct(int $value)
    {
        parent::__construct('dbal.netsting_level', $value);
    }
}

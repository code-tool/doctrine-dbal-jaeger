<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Tag;

use Jaeger\Tag\StringTag;

class DbalErrorCodeTag extends StringTag
{
    public function __construct($value)
    {
        parent::__construct('dbal.error', (string)$value);
    }
}

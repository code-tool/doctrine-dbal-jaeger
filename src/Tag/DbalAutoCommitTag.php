<?php
declare(strict_types=1);

namespace Doctrine\DBAL\Jaeger\Tag;

use Jaeger\Tag\BoolTag;

class DbalAutoCommitTag extends BoolTag
{
    public function __construct(bool $value)
    {
        parent::__construct('dbal.auto_commit', $value);
    }
}

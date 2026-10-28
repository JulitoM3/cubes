<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Graph;

use Julitom3\Cubes\Enums\JoinType;

final readonly class Edge
{
    public function __construct(
        public string $from,
        public string $to,
        public string $fromTable,
        public string $toTable,
        public string $localKey,
        public string $foreignKey,
        public JoinType $type,
    ) {}
}

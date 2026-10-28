<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

use Julitom3\Cubes\Enums\JoinType;
use Julitom3\Cubes\Graph\Edge;

/**
 * One JOIN clause: "{type} join {table} on {from}.{localKey} = {table}.{foreignKey}".
 */
final readonly class JoinDefinitionDto
{
    public function __construct(
        public string $table,
        public string $from,
        public string $localKey,
        public string $foreignKey,
        public JoinType $type,
    ) {}

    public static function createFromEdge(Edge $edge): self
    {
        return new self(
            table: $edge->toTable,
            from: $edge->fromTable,
            localKey: $edge->localKey,
            foreignKey: $edge->foreignKey,
            type: $edge->type,
        );
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph;

use Julitom3\Cubes\Datasets\Enums\JoinType;

/**
 * A relation walked in the direction of traversal: "{from}.{localKey} = {to}.{foreignKey}".
 *
 * The local key always belongs to the "from" table and the foreign key to the "to" table,
 * whichever dataset declared the relation; the graph swaps the keys of the reverse edge.
 */
final readonly class DatasetEdgeDto
{
    public function __construct(
        public string $from,
        public string $to,
        public string $localKey,
        public string $foreignKey,
        public JoinType $joinType,
    ) {}

    /**
     * The same relation walked from the other end.
     */
    public function reverse(): self
    {
        return new self(
            from: $this->to,
            to: $this->from,
            localKey: $this->foreignKey,
            foreignKey: $this->localKey,
            joinType: $this->joinType,
        );
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph;

use Julitom3\Cubes\Datasets\Contracts\DatasetContract;

/**
 * A dataset in the graph, identified by its table, with the edges that leave it.
 */
final readonly class DatasetNodeDto
{
    /**
     * @param  list<DatasetEdgeDto>  $edges
     */
    public function __construct(
        public string $table,
        public DatasetContract $dataset,
        public array $edges = [],
    ) {}

    /**
     * The first edge that leads to the given table, if any.
     */
    public function getEdge(string $table): ?DatasetEdgeDto
    {
        foreach ($this->edges as $edge) {
            if ($edge->to === $table) {
                return $edge;
            }
        }

        return null;
    }

    public function degree(): int
    {
        return count($this->edges);
    }
}

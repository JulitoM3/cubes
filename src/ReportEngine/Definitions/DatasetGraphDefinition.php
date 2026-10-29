<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions;

use Julitom3\Cubes\ReportEngine\DatasetRegistry;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetEdgeDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetGraphDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetNodeDto;

/**
 * Build the graph of the registered datasets, where every relation can be walked in both directions.
 *
 * It is a singleton that keeps the graph until another dataset is registered.
 */
final class DatasetGraphDefinition
{
    private ?DatasetGraphDefinitionDto $graph = null;

    private ?int $version = null;

    public function __construct(private readonly DatasetRegistry $datasetRegistry) {}

    public function create(): DatasetGraphDefinitionDto
    {
        if ($this->graph !== null && $this->version === $this->datasetRegistry->version()) {
            return $this->graph;
        }

        $this->version = $this->datasetRegistry->version();

        return $this->graph = $this->build();
    }

    private function build(): DatasetGraphDefinitionDto
    {
        $datasets = [];
        /** @var array<string, list<DatasetEdgeDto>> $edges */
        $edges = [];

        foreach ($this->datasetRegistry->all() as $dataset) {
            $datasets[$dataset->table()] = $dataset;
            $edges[$dataset->table()] = [];
        }

        foreach ($datasets as $table => $dataset) {
            foreach ($dataset->relations() as $relation) {
                // Relations to tables without a registered dataset cannot be reported on.
                if (! isset($datasets[$relation->table]) || $relation->table === $table) {
                    continue;
                }

                $edge = new DatasetEdgeDto(
                    from: $table,
                    to: $relation->table,
                    localKey: $relation->localKey,
                    foreignKey: $relation->foreignKey,
                    joinType: $relation->joinType,
                );

                $edges[$edge->from][] = $edge;
                $edges[$edge->to][] = $edge->reverse();
            }
        }

        $nodes = [];

        foreach ($datasets as $table => $dataset) {
            $nodes[$table] = new DatasetNodeDto($table, $dataset, $edges[$table]);
        }

        return new DatasetGraphDefinitionDto($nodes);
    }
}

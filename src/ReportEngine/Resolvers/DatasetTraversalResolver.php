<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Resolvers;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetGraphDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetNodeDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetRootDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ValueDefinitionDto;

/**
 * Choose the root node of the query and the nodes it has to reach.
 *
 * The root is the explicit base dataset, or else the dataset of the first value (the fact
 * being measured), or else the selected dataset with the most edges. The degree is a
 * heuristic to start from a well-connected dataset, not a guarantee of the best root.
 */
final readonly class DatasetTraversalResolver
{
    public function resolve(DatasetGraphDefinitionDto $graph, ReportDefinitionDto $report): DatasetRootDto
    {
        $nodes = $this->selectedNodes($graph, $report);
        $root = $this->root($graph, $report, $nodes);

        return new DatasetRootDto(
            root: $root,
            destinations: array_values(array_filter(
                $nodes,
                fn (DatasetNodeDto $node): bool => $node->table !== $root->table,
            )),
        );
    }

    /**
     * The distinct nodes of the selected fields, in selection order.
     *
     * @return list<DatasetNodeDto>
     */
    private function selectedNodes(DatasetGraphDefinitionDto $graph, ReportDefinitionDto $report): array
    {
        $nodes = [];

        foreach ([...$report->dimensions(), ...$report->values] as $selection) {
            /** @var DimensionDefinitionDto|ValueDefinitionDto $selection */
            $table = $selection->dataset->table();
            $nodes[$table] ??= $graph->getNodeByTable($table);
        }

        return array_values($nodes);
    }

    /**
     * @param  list<DatasetNodeDto>  $nodes
     */
    private function root(DatasetGraphDefinitionDto $graph, ReportDefinitionDto $report, array $nodes): DatasetNodeDto
    {
        if ($report->from !== null) {
            return $graph->getNodeByTable($report->from->table());
        }

        if ($report->values !== []) {
            return $graph->getNodeByTable($report->values[0]->dataset->table());
        }

        $root = $nodes[0];

        foreach ($nodes as $node) {
            if ($node->degree() > $root->degree()) {
                $root = $node;
            }
        }

        return $root;
    }
}

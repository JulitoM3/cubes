<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions;

use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetGraphDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetNodeDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetPathDto;
use SplQueue;

/**
 * Find the shortest path between two nodes with a breadth-first search keyed by table.
 */
final readonly class DatasetPathDefinition
{
    public function create(
        DatasetGraphDefinitionDto $graph,
        DatasetNodeDto $origin,
        DatasetNodeDto $destination,
    ): DatasetPathDto {
        if ($origin->table === $destination->table) {
            return new DatasetPathDto([$origin]);
        }

        /** @var SplQueue<DatasetNodeDto> $queue */
        $queue = new SplQueue;
        $queue->enqueue($origin);

        $visited = [$origin->table => true];
        /** @var array<string, DatasetNodeDto> $parents */
        $parents = [];

        while (! $queue->isEmpty()) {
            $current = $queue->dequeue();

            foreach ($current->edges as $edge) {
                $neighbor = $graph->getNodeByTable($edge->to);

                if (isset($visited[$neighbor->table])) {
                    continue;
                }

                $visited[$neighbor->table] = true;
                $parents[$neighbor->table] = $current;

                if ($neighbor->table === $destination->table) {
                    return $this->buildPath($parents, $origin, $neighbor);
                }

                $queue->enqueue($neighbor);
            }
        }

        throw CubesException::noPath($origin->dataset->category(), $destination->dataset->category());
    }

    /**
     * @param  array<string, DatasetNodeDto>  $parents
     */
    private function buildPath(array $parents, DatasetNodeDto $origin, DatasetNodeDto $destination): DatasetPathDto
    {
        $path = [];

        for ($current = $destination; $current->table !== $origin->table; $current = $parents[$current->table]) {
            $path[] = $current;
        }

        $path[] = $origin;

        return new DatasetPathDto(array_reverse($path));
    }
}

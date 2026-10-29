<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\Factories;

use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\ReportEngine\Definitions\DatasetPathDefinition;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetEdgeDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetGraphDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetRootDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\JoinDefinitionDto;

/**
 * Turn the path from the root to every destination into joins, joining each table once.
 *
 * Paths are walked in destination order and pair by pair, so every join connects the
 * table it adds to a table that is already in the query: the root or an earlier join.
 */
final readonly class JoinDefinitionFactory
{
    public function __construct(private DatasetPathDefinition $datasetPathDefinition) {}

    /**
     * @return list<JoinDefinitionDto>
     */
    public function createDefinition(DatasetRootDto $datasetRoot, DatasetGraphDefinitionDto $graph): array
    {
        $joined = [$datasetRoot->root->table => true];
        $joins = [];

        foreach ($datasetRoot->destinations as $destination) {
            $path = $this->datasetPathDefinition->create($graph, $datasetRoot->root, $destination);

            foreach ($path->pairs() as [$origin, $next]) {
                if (isset($joined[$next->table])) {
                    continue;
                }

                // The path was found through this edge, so it always exists.
                $edge = $origin->getEdge($next->table)
                    ?? throw CubesException::noPath($origin->dataset->category(), $next->dataset->category());

                $joined[$next->table] = true;
                $joins[] = $this->createFromEdge($edge);
            }
        }

        return $joins;
    }

    /**
     * The edge is walked from a table already in the query, so it joins the "to" table.
     */
    private function createFromEdge(DatasetEdgeDto $edge): JoinDefinitionDto
    {
        return new JoinDefinitionDto(
            table: $edge->to,
            from: $edge->from,
            localKey: $edge->localKey,
            foreignKey: $edge->foreignKey,
            joinType: $edge->joinType,
        );
    }
}

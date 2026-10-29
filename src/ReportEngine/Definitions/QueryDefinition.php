<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions;

use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\FromDefinitionFactory;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\GroupByDefinitionFactory;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\JoinDefinitionFactory;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\OrderByDefinitionFactory;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\SelectDefinitionFactory;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\ValueDefinitionFactory;
use Julitom3\Cubes\ReportEngine\Resolvers\DatasetTraversalResolver;

/**
 * Turn a report definition into the query definition the SQL compiler runs.
 *
 * It only orchestrates: the graph, the root, the paths, and each clause belong to their own collaborator.
 */
final readonly class QueryDefinition
{
    public function __construct(
        private DatasetGraphDefinition $datasetGraphDefinition,
        private DatasetTraversalResolver $datasetTraversalResolver,
        private SelectDefinitionFactory $selectDefinitionFactory,
        private ValueDefinitionFactory $valueDefinitionFactory,
        private FromDefinitionFactory $fromDefinitionFactory,
        private JoinDefinitionFactory $joinDefinitionFactory,
        private GroupByDefinitionFactory $groupByDefinitionFactory,
        private OrderByDefinitionFactory $orderByDefinitionFactory,
    ) {}

    public function create(ReportDefinitionDto $reportDefinition): QueryDefinitionDto
    {
        if ($reportDefinition->isEmpty()) {
            throw CubesException::emptyReport();
        }

        $graph = $this->datasetGraphDefinition->create();
        $datasetRoot = $this->datasetTraversalResolver->resolve($graph, $reportDefinition);
        $dimensions = $reportDefinition->dimensions();

        return new QueryDefinitionDto(
            from: $this->fromDefinitionFactory->createDefinition($datasetRoot),
            selects: [
                ...$this->selectDefinitionFactory->create($dimensions),
                ...$this->valueDefinitionFactory->create($reportDefinition->values),
            ],
            joins: $this->joinDefinitionFactory->createDefinition($datasetRoot, $graph),
            groupBy: $this->groupByDefinitionFactory->create($dimensions),
            orders: $this->orderByDefinitionFactory->create($dimensions),
        );
    }
}

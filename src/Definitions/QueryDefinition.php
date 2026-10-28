<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions;

use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\DatasetRegistry;
use Julitom3\Cubes\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\FromDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\GroupByDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\JoinDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\OrderByDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\SelectDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\ValueDefinitionDto;
use Julitom3\Cubes\Exceptions\CubesException;

/**
 * Turn a report definition into the query definition the SQL compiler runs.
 */
final readonly class QueryDefinition
{
    public function __construct(private DatasetRegistry $registry) {}

    /**
     * The alias of the dimension at the given position in the select list.
     */
    public static function dimensionAlias(int $position): string
    {
        return "d{$position}";
    }

    /**
     * The alias of the value at the given position in the select list.
     */
    public static function valueAlias(int $position): string
    {
        return "v{$position}";
    }

    public function create(ReportDefinitionDto $report): QueryDefinitionDto
    {
        if ($report->isEmpty()) {
            throw CubesException::emptyReport();
        }

        $root = $this->root($report);
        $dimensions = $report->dimensions();

        return new QueryDefinitionDto(
            from: FromDefinitionDto::createFromDataset($root),
            selects: [
                ...array_map(
                    fn (DimensionDefinitionDto $dimension, int $position): SelectDefinitionDto => SelectDefinitionDto::createFromDimension($dimension, self::dimensionAlias($position)),
                    $dimensions,
                    array_keys($dimensions),
                ),
                ...array_map(
                    fn (ValueDefinitionDto $value, int $position): SelectDefinitionDto => SelectDefinitionDto::createFromValue($value, self::valueAlias($position)),
                    $report->values,
                    array_keys($report->values),
                ),
            ],
            joins: $this->joins($root, [...$dimensions, ...$report->values]),
            groupBy: array_map(GroupByDefinitionDto::createFromDimension(...), $dimensions),
            orders: array_map(OrderByDefinitionDto::createFromDimension(...), $dimensions),
        );
    }

    /**
     * Use the explicit base dataset, or the dataset of the first value (the fact being measured),
     * or else the most connected dataset of the report.
     */
    private function root(ReportDefinitionDto $report): Dataset
    {
        if ($report->from !== null) {
            return $this->registry->get($report->from);
        }

        if ($report->values !== []) {
            return $report->values[0]->dataset;
        }

        $graph = $this->registry->graph();
        $dimensions = $report->dimensions();
        $root = $dimensions[0]->dataset;

        foreach ($dimensions as $dimension) {
            if ($graph->degree($dimension->dataset->name()) > $graph->degree($root->name())) {
                $root = $dimension->dataset;
            }
        }

        return $root;
    }

    /**
     * Join every dataset the report uses through the shortest path, joining each table only once.
     *
     * @param  list<DimensionDefinitionDto|ValueDefinitionDto>  $selections
     * @return list<JoinDefinitionDto>
     */
    private function joins(Dataset $root, array $selections): array
    {
        $graph = $this->registry->graph();
        $joined = [$root->name() => true];
        $joins = [];

        foreach ($selections as $selection) {
            foreach ($graph->path($root->name(), $selection->dataset->name()) as $edge) {
                if (isset($joined[$edge->to])) {
                    continue;
                }

                $joined[$edge->to] = true;
                $joins[] = JoinDefinitionDto::createFromEdge($edge);
            }
        }

        return $joins;
    }
}

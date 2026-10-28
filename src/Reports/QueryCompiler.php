<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Reports;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\DatasetRegistry;
use Julitom3\Cubes\Exceptions\CubesException;

final readonly class QueryCompiler
{
    public function __construct(
        private DatasetRegistry $registry,
        private DatabaseManager $database,
        private Repository $config,
    ) {}

    /**
     * The alias of the dimension at the given position in the select list.
     */
    public static function dimensionAlias(int $position): string
    {
        return "d{$position}";
    }

    /**
     * The alias of the measure at the given position in the select list.
     */
    public static function measureAlias(int $position): string
    {
        return "m{$position}";
    }

    public function compile(Report $report): Builder
    {
        $dimensions = [...$report->rowDimensions(), ...$report->columnDimensions()];
        $measures = $report->measures();

        if ($dimensions === [] && $measures === []) {
            throw CubesException::emptyReport();
        }

        $root = $this->root($report, $dimensions, $measures);

        $connection = $this->config->get('cubes.connection');
        $query = $this->database
            ->connection(is_string($connection) ? $connection : null)
            ->query()
            ->from($root->table());

        $this->joins($query, $root, [...$dimensions, ...$measures]);

        foreach ($dimensions as $position => $dimension) {
            $query->addSelect(new FieldExpression($dimension->dataset, $dimension->field, alias: self::dimensionAlias($position)))
                ->groupBy(new FieldExpression($dimension->dataset, $dimension->field))
                ->orderBy(new FieldExpression($dimension->dataset, $dimension->field));
        }

        foreach ($measures as $position => $measure) {
            $query->addSelect(new FieldExpression($measure->dataset, $measure->field, $measure->aggregation, self::measureAlias($position)));
        }

        return $query;
    }

    /**
     * Use the explicit root, or the dataset of the first value (the fact being measured),
     * or else the most connected dataset of the report.
     *
     * @param  list<Dimension>  $dimensions
     * @param  list<Measure>  $measures
     */
    private function root(Report $report, array $dimensions, array $measures): Dataset
    {
        if ($report->root() !== null) {
            return $this->registry->get($report->root());
        }

        if ($measures !== []) {
            return $measures[0]->dataset;
        }

        $graph = $this->registry->graph();
        $root = $dimensions[0]->dataset;

        foreach ($dimensions as $dimension) {
            if ($graph->degree($dimension->dataset->name()) > $graph->degree($root->name())) {
                $root = $dimension->dataset;
            }
        }

        return $root;
    }

    /**
     * Join every dataset the report uses, joining each table only once.
     *
     * @param  list<Dimension|Measure>  $selections
     */
    private function joins(Builder $query, Dataset $root, array $selections): void
    {
        $graph = $this->registry->graph();
        $joined = [$root->name() => true];

        foreach ($selections as $selection) {
            foreach ($graph->path($root->name(), $selection->dataset->name()) as $edge) {
                if (isset($joined[$edge->to])) {
                    continue;
                }

                $joined[$edge->to] = true;

                $query->join(
                    $edge->toTable,
                    "{$edge->fromTable}.{$edge->localKey}",
                    '=',
                    "{$edge->toTable}.{$edge->foreignKey}",
                    $edge->type->value,
                );
            }
        }
    }
}

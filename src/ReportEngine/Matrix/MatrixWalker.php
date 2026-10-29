<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix;

use Illuminate\Support\Collection;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixAxisDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLevelDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixValueDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Nodes\MatrixNodeDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Tree\MatrixMetricDto;

/**
 * Build the tree of one axis from the query records.
 */
final readonly class MatrixWalker
{
    /**
     * @param  Collection<int, array<string, mixed>>  $records  Query records keyed by select alias.
     * @param  MatrixAxisDefinitionDto|null  $crossAxis  The other axis, to key the metrics of each leaf by its leaf.
     * @param  list<MatrixValueDefinitionDto>  $values  The metrics to attach to the leaves.
     */
    public function walk(
        MatrixAxisDefinitionDto $axis,
        Collection $records,
        ?MatrixAxisDefinitionDto $crossAxis = null,
        array $values = [],
    ): MatrixNodeDto {
        $root = MatrixNodeDto::root();

        foreach ($records as $record) {
            $leaf = $this->walkRecord($root, $axis, $record);

            if ($crossAxis !== null) {
                $this->attachMetrics($leaf, $crossAxis, $values, $record);
            }
        }

        return $root;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function walkRecord(MatrixNodeDto $root, MatrixAxisDefinitionDto $axis, array $record): MatrixNodeDto
    {
        $current = $root;

        foreach ($axis->levels as $level) {
            $value = $record[$level->alias] ?? null;
            $child = $current->child($value);

            if ($child === null) {
                $child = new MatrixNodeDto(matrixLevelDefinitionDto: $level, value: $value, parent: $current);
                $current->addChild($child);
            }

            $current = $child;
        }

        return $current;
    }

    /**
     * @param  list<MatrixValueDefinitionDto>  $values
     * @param  array<string, mixed>  $record
     */
    private function attachMetrics(MatrixNodeDto $leaf, MatrixAxisDefinitionDto $crossAxis, array $values, array $record): void
    {
        $crossPath = array_map(fn (MatrixLevelDefinitionDto $level): mixed => $record[$level->alias] ?? null, $crossAxis->levels);

        $leaf->metrics[MatrixNodeDto::keyOf($crossPath)] = array_map(
            fn (MatrixValueDefinitionDto $value): MatrixMetricDto => new MatrixMetricDto($value, $record[$value->alias] ?? null),
            $values,
        );
    }
}

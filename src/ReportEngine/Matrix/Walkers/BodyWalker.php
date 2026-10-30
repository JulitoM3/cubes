<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix\Walkers;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixBodyDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixBodyRowDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixHeaderCellDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLayoutDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixValueCellDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Nodes\MatrixNodeDto;
use Julitom3\Cubes\ReportEngine\Matrix\MatrixLabelFormatter;

/**
 * One body row per row leaf, with one cell per column leaf and value, in header order.
 *
 * It needs the whole layout: the cells follow the leaves of the column tree, so a
 * combination missing from the records still gets an empty cell under its header.
 */
final readonly class BodyWalker
{
    public function __construct(private MatrixLabelFormatter $labelFormatter) {}

    public function walk(MatrixLayoutDto $layout): MatrixBodyDto
    {
        $rows = [];
        $columnLeaves = $layout->columnLeaves();

        if ($layout->rowTree->isLeaf()) {
            // Without row dimensions every record lands on the root: a single totals row.
            if ($layout->rowTree->metrics !== []) {
                $rows[] = $this->row($layout, $layout->rowTree, [], $columnLeaves);
            }

            return new MatrixBodyDto($rows);
        }

        foreach ($layout->rowTree->children as $child) {
            $this->walkNode($layout, $child, [], $columnLeaves, $rows);
        }

        return new MatrixBodyDto($rows);
    }

    /**
     * @param  list<MatrixHeaderCellDto>  $headers
     * @param  list<MatrixNodeDto>  $columnLeaves
     * @param  list<MatrixBodyRowDto>  $rows
     */
    private function walkNode(MatrixLayoutDto $layout, MatrixNodeDto $node, array $headers, array $columnLeaves, array &$rows): void
    {
        $headers[] = new MatrixHeaderCellDto(
            label: $this->labelFormatter->format($node->value),
            kind: MatrixHeaderCellDto::DIMENSION,
            depth: $node->depth(),
            isLeaf: $node->isLeaf(),
            value: $node->value,
        );

        if ($node->isLeaf()) {
            $rows[] = $this->row($layout, $node, $headers, $columnLeaves);

            return;
        }

        foreach ($node->children as $child) {
            $this->walkNode($layout, $child, $headers, $columnLeaves, $rows);
        }
    }

    /**
     * @param  list<MatrixHeaderCellDto>  $headers
     * @param  list<MatrixNodeDto>  $columnLeaves
     */
    private function row(MatrixLayoutDto $layout, MatrixNodeDto $rowLeaf, array $headers, array $columnLeaves): MatrixBodyRowDto
    {
        $cells = [];

        foreach ($columnLeaves as $columnLeaf) {
            $metrics = $rowLeaf->metricsFor($columnLeaf->key());

            foreach ($layout->definition->values as $position => $value) {
                $cells[] = new MatrixValueCellDto(
                    value: $metrics[$position]->value ?? null,
                    format: $value->value->field->format()->value,
                );
            }
        }

        return new MatrixBodyRowDto($headers, $cells);
    }
}

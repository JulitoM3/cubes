<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix\Walkers;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixHeaderCellDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLayoutDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Nodes\MatrixNodeDto;
use Julitom3\Cubes\ReportEngine\Matrix\MatrixLabelFormatter;

/**
 * One header row per column level, plus a last row with the value labels when needed.
 *
 * A column cell spans every value of every leaf below it.
 */
final readonly class ColumnHeaderWalker
{
    public function __construct(private MatrixLabelFormatter $labelFormatter) {}

    /**
     * @return array<int, list<MatrixHeaderCellDto>>
     */
    public function walk(MatrixLayoutDto $layout): array
    {
        $rows = [];
        $valueSpan = max(1, count($layout->definition->values));

        foreach ($layout->columnTree->children as $child) {
            $this->walkNode($child, $valueSpan, $rows);
        }

        if ($layout->definition->showsValueHeaders()) {
            $depth = $layout->definition->headerDepth() - 1;

            foreach ($layout->columnLeaves() as $leaf) {
                foreach ($layout->definition->values as $value) {
                    $rows[$depth][] = new MatrixHeaderCellDto(
                        label: $value->value->label(),
                        kind: MatrixHeaderCellDto::MEASURE,
                        depth: $depth,
                        isLeaf: true,
                    );
                }
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, list<MatrixHeaderCellDto>>  $rows
     */
    private function walkNode(MatrixNodeDto $node, int $valueSpan, array &$rows): void
    {
        $rows[$node->depth()][] = new MatrixHeaderCellDto(
            label: $this->labelFormatter->format($node->value),
            kind: MatrixHeaderCellDto::COLUMN,
            depth: $node->depth(),
            colspan: count($node->leaves()) * $valueSpan,
            isLeaf: $node->isLeaf(),
            value: $node->value,
        );

        foreach ($node->children as $child) {
            $this->walkNode($child, $valueSpan, $rows);
        }
    }
}

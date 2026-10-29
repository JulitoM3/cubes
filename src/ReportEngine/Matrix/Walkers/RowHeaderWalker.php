<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix\Walkers;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixHeaderCellDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLayoutDto;

/**
 * The labels of the row dimensions, on the first header row, spanning every header row.
 */
final readonly class RowHeaderWalker
{
    /**
     * @return array<int, list<MatrixHeaderCellDto>>
     */
    public function walk(MatrixLayoutDto $layout): array
    {
        $headers = [];

        foreach ($layout->definition->rows->levels as $level) {
            $headers[0][] = new MatrixHeaderCellDto(
                label: $level->field->label(),
                kind: MatrixHeaderCellDto::DIMENSION,
                depth: $level->depth,
                rowspan: $layout->definition->headerDepth(),
            );
        }

        return $headers;
    }
}

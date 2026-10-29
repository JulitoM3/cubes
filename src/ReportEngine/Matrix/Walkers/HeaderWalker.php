<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix\Walkers;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixHeaderCellDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLayoutDto;

/**
 * Combine the row headers and the column headers into the header rows of the table.
 */
final readonly class HeaderWalker
{
    public function __construct(
        private ColumnHeaderWalker $columnHeaderWalker,
        private RowHeaderWalker $rowHeaderWalker,
    ) {}

    /**
     * @return list<list<MatrixHeaderCellDto>>
     */
    public function walk(MatrixLayoutDto $layout): array
    {
        $rows = $this->rowHeaderWalker->walk($layout);

        foreach ($this->columnHeaderWalker->walk($layout) as $depth => $cells) {
            $rows[$depth] = [...$rows[$depth] ?? [], ...$cells];
        }

        ksort($rows);

        return array_values($rows);
    }
}

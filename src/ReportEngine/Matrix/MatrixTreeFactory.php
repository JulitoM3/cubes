<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix;

use Illuminate\Support\Collection;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLayoutDto;

final readonly class MatrixTreeFactory
{
    public function __construct(private MatrixWalker $matrixWalker) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $records  Query records keyed by select alias.
     */
    public function create(MatrixDefinitionDto $matrixDefinition, Collection $records): MatrixLayoutDto
    {
        $rowTree = $this->matrixWalker->walk(
            $matrixDefinition->rows,
            $records,
            crossAxis: $matrixDefinition->columns,
            values: $matrixDefinition->values,
        );

        $columnTree = $this->matrixWalker->walk($matrixDefinition->columns, $records);

        // Records come ordered by the row dimensions first, so the column tree needs its own sort.
        $columnTree->sortChildren();

        return new MatrixLayoutDto(
            definition: $matrixDefinition,
            rowTree: $rowTree,
            columnTree: $columnTree,
        );
    }
}

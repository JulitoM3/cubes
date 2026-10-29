<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixAxisDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLevelDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixValueDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ValueDefinitionDto;

/**
 * Describe the axes and values of the matrix, reading each alias from the query definition.
 */
final readonly class MatrixDefinition
{
    public function create(ReportDefinitionDto $reportDefinition, QueryDefinitionDto $queryDefinition): MatrixDefinitionDto
    {
        return new MatrixDefinitionDto(
            rows: $this->resolveAxis($reportDefinition->rows, $queryDefinition),
            columns: $this->resolveAxis($reportDefinition->columns, $queryDefinition),
            values: $this->resolveValues($reportDefinition->values, $queryDefinition),
        );
    }

    /**
     * @param  list<DimensionDefinitionDto>  $dimensions
     */
    private function resolveAxis(array $dimensions, QueryDefinitionDto $queryDefinition): MatrixAxisDefinitionDto
    {
        return new MatrixAxisDefinitionDto(array_map(
            fn (DimensionDefinitionDto $dimension, int $depth): MatrixLevelDefinitionDto => new MatrixLevelDefinitionDto(
                field: $dimension,
                depth: $depth,
                alias: $queryDefinition->aliasOf($dimension),
            ),
            $dimensions,
            array_keys($dimensions),
        ));
    }

    /**
     * @param  list<ValueDefinitionDto>  $values
     * @return list<MatrixValueDefinitionDto>
     */
    private function resolveValues(array $values, QueryDefinitionDto $queryDefinition): array
    {
        return array_map(
            fn (ValueDefinitionDto $value): MatrixValueDefinitionDto => new MatrixValueDefinitionDto(
                value: $value,
                alias: $queryDefinition->aliasOf($value),
            ),
            $values,
        );
    }
}

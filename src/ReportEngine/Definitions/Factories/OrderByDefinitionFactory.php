<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\Factories;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\OrderByDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\Contracts\DefinitionFactoryContract;

/**
 * Order ascending by every row and column dimension, so the rows of the matrix come out sorted.
 *
 * @extends DefinitionFactoryContract<DimensionDefinitionDto, OrderByDefinitionDto>
 */
final class OrderByDefinitionFactory extends DefinitionFactoryContract
{
    protected function createDefinition(mixed $item, int $position): OrderByDefinitionDto
    {
        return new OrderByDefinitionDto(
            table: $item->dataset->table(),
            column: $item->field->column,
            expression: $item->field->expression,
        );
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\Factories;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\GroupByDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\Contracts\DefinitionFactoryContract;

/**
 * Group by every row and column dimension, using its expression when it has one.
 *
 * @extends DefinitionFactoryContract<DimensionDefinitionDto, GroupByDefinitionDto>
 */
final class GroupByDefinitionFactory extends DefinitionFactoryContract
{
    protected function createDefinition(mixed $item, int $position): GroupByDefinitionDto
    {
        return new GroupByDefinitionDto(
            table: $item->dataset->table(),
            column: $item->field->column,
            expression: $item->field->expression,
        );
    }
}

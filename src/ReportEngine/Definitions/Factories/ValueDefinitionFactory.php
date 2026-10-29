<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\Factories;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\SelectDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ValueDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\Factories\Contracts\DefinitionFactoryContract;

/**
 * Select every value with its aggregation, aliased "v{position}".
 *
 * @extends DefinitionFactoryContract<ValueDefinitionDto, SelectDefinitionDto>
 */
final class ValueDefinitionFactory extends DefinitionFactoryContract
{
    protected function createDefinition(mixed $item, int $position): SelectDefinitionDto
    {
        return new SelectDefinitionDto(
            origin: $item,
            table: $item->dataset->table(),
            column: $item->field->column,
            expression: $item->field->expression,
            aggregation: $item->aggregation,
            alias: "v{$position}",
        );
    }
}

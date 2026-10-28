<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

/**
 * One column of the GROUP BY clause, one per dimension of the report.
 */
final readonly class GroupByDefinitionDto
{
    public function __construct(
        public string $table,
        public string $column,
        public ?string $expression,
    ) {}

    public static function createFromDimension(DimensionDefinitionDto $dimension): self
    {
        return new self(
            table: $dimension->dataset->table(),
            column: $dimension->field->column,
            expression: $dimension->field->expression,
        );
    }

    public function hasExpression(): bool
    {
        return $this->expression !== null;
    }
}

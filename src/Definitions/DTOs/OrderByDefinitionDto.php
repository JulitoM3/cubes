<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

/**
 * One column of the ORDER BY clause, so the rows of the matrix come out sorted.
 */
final readonly class OrderByDefinitionDto
{
    /**
     * @param  'asc'|'desc'  $direction
     */
    public function __construct(
        public string $table,
        public string $column,
        public ?string $expression,
        public string $direction = 'asc',
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

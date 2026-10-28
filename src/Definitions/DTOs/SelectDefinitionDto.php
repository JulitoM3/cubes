<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

use Julitom3\Cubes\Enums\Aggregation;

/**
 * One column of the SELECT clause. Every select is aliased, so results never depend on column names.
 */
final readonly class SelectDefinitionDto
{
    public function __construct(
        public string $table,
        public string $column,
        public ?string $expression,
        public ?Aggregation $aggregation,
        public string $alias,
    ) {}

    public static function createFromDimension(DimensionDefinitionDto $dimension, string $alias): self
    {
        return new self(
            table: $dimension->dataset->table(),
            column: $dimension->field->column,
            expression: $dimension->field->expression,
            aggregation: null,
            alias: $alias,
        );
    }

    public static function createFromValue(ValueDefinitionDto $value, string $alias): self
    {
        return new self(
            table: $value->dataset->table(),
            column: $value->field->column,
            expression: $value->field->expression,
            aggregation: $value->aggregation,
            alias: $alias,
        );
    }

    public function hasExpression(): bool
    {
        return $this->expression !== null;
    }

    public function hasAggregation(): bool
    {
        return $this->aggregation !== null;
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\DTOs;

use Illuminate\Support\Str;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Datasets\Enums\FieldType;
use Julitom3\Cubes\Datasets\Support\Identifier;
use Julitom3\Cubes\Exceptions\CubesException;

final readonly class FieldDefinitionDto
{
    public string $label;

    /**
     * @param  list<Aggregation>  $aggregations  Aggregations allowed when the field is used as a value.
     * @param  string|null  $expression  Trusted SQL used instead of the column, e.g. "YEAR(sales.sold_at)".
     */
    public function __construct(
        public string $column,
        ?string $label = null,
        public FieldType $type = FieldType::Dimension,
        public FieldFormat $format = FieldFormat::Text,
        public array $aggregations = [],
        public bool $pivotable = false,
        public ?string $expression = null,
    ) {
        if (! Identifier::isValid($column)) {
            throw CubesException::invalidIdentifier($column);
        }

        $this->label = $label ?? Str::headline($column);
    }

    /**
     * Define a field that rows and columns can be grouped by.
     */
    public static function dimension(
        string $column,
        ?string $label = null,
        FieldFormat $format = FieldFormat::Text,
        bool $pivotable = false,
        ?string $expression = null,
    ): self {
        return new self(
            column: $column,
            label: $label,
            type: FieldType::Dimension,
            format: $format,
            pivotable: $pivotable,
            expression: $expression,
        );
    }

    /**
     * Define a numeric field that values are aggregated from.
     *
     * @param  list<Aggregation>  $aggregations
     */
    public static function metric(
        string $column,
        ?string $label = null,
        array $aggregations = [Aggregation::Sum, Aggregation::Avg, Aggregation::Min, Aggregation::Max],
        FieldFormat $format = FieldFormat::Integer,
        ?string $expression = null,
    ): self {
        return new self(
            column: $column,
            label: $label,
            type: FieldType::Metric,
            format: $format,
            aggregations: $aggregations,
            expression: $expression,
        );
    }

    public function isDimension(): bool
    {
        return $this->type === FieldType::Dimension;
    }

    /**
     * The aggregations allowed for this field, falling back to COUNT.
     *
     * @return non-empty-list<Aggregation>
     */
    public function allowedAggregations(): array
    {
        return $this->aggregations !== [] ? $this->aggregations : [Aggregation::Count];
    }

    public function allows(Aggregation $aggregation): bool
    {
        return in_array($aggregation, $this->allowedAggregations(), true);
    }

    public function defaultAggregation(): Aggregation
    {
        return $this->allowedAggregations()[0];
    }
}

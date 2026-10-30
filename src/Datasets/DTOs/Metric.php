<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\DTOs;

use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Datasets\Enums\FieldType;

/**
 * A numeric field that values are aggregated from.
 */
final readonly class Metric extends FieldDefinitionDto
{
    /**
     * @param  list<Aggregation>  $aggregations  The aggregations allowed as a value; the first is the default.
     * @param  FieldFormat|null  $format  Null to take the format from the cast of the column.
     * @param  string|null  $expression  Trusted SQL used instead of the column, e.g. "sales.quantity * sales.unit_price".
     */
    public function __construct(
        string $column,
        ?string $label = null,
        public array $aggregations = [Aggregation::Sum, Aggregation::Avg, Aggregation::Min, Aggregation::Max],
        ?FieldFormat $format = null,
        ?string $expression = null,
    ) {
        parent::__construct($column, $label, $format, $expression);
    }

    public function type(): FieldType
    {
        return FieldType::Metric;
    }

    public function withFormat(FieldFormat $format): static
    {
        return new self($this->column, $this->label, $this->aggregations, $format, $this->expression);
    }

    public function defaultFormat(): FieldFormat
    {
        return FieldFormat::Integer;
    }

    public function allowedAggregations(): array
    {
        return $this->aggregations !== [] ? $this->aggregations : parent::allowedAggregations();
    }
}

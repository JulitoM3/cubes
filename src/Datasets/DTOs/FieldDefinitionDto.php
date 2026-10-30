<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\DTOs;

use Illuminate\Support\Str;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Datasets\Enums\FieldType;
use Julitom3\Cubes\Datasets\Support\Identifier;
use Julitom3\Cubes\Exceptions\CubesException;

/**
 * A field of a dataset, declared as a Dimension or a Metric in the cube schema of a model.
 */
abstract readonly class FieldDefinitionDto
{
    public string $label;

    /**
     * @param  FieldFormat|null  $format  Null to take the format from the cast of the column.
     * @param  string|null  $expression  Trusted SQL used instead of the column, e.g. "YEAR(sales.sold_at)".
     */
    public function __construct(
        public string $column,
        ?string $label = null,
        public ?FieldFormat $format = null,
        public ?string $expression = null,
    ) {
        if (! Identifier::isValid($column)) {
            throw CubesException::invalidIdentifier($column);
        }

        $this->label = $label ?? Str::headline($column);
    }

    abstract public function type(): FieldType;

    /**
     * The same field with the given format.
     */
    abstract public function withFormat(FieldFormat $format): static;

    /**
     * The format used when none is declared and the column has no known cast.
     */
    abstract public function defaultFormat(): FieldFormat;

    public function format(): FieldFormat
    {
        return $this->format ?? $this->defaultFormat();
    }

    public function isDimension(): bool
    {
        return $this->type() === FieldType::Dimension;
    }

    /**
     * The aggregations allowed when the field is used as a value.
     *
     * @return non-empty-list<Aggregation>
     */
    public function allowedAggregations(): array
    {
        return [Aggregation::Count];
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

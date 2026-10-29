<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs;

use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\Aggregation;

/**
 * A field aggregated as a value of a report. The label is only displayed, never compiled to SQL.
 */
final readonly class ValueDefinitionDto
{
    public function __construct(
        public DatasetContract $dataset,
        public FieldDefinitionDto $field,
        public Aggregation $aggregation,
        public ?string $customLabel = null,
    ) {}

    /**
     * The stable key of the value, e.g. "sum(sales.amount)".
     */
    public function key(): string
    {
        return "{$this->aggregation->value}({$this->dataset->reference($this->field)})";
    }

    public function label(): string
    {
        return $this->customLabel ?? $this->field->label;
    }
}

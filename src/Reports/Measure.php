<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Reports;

use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;
use Julitom3\Cubes\Enums\Aggregation;

final readonly class Measure
{
    public function __construct(
        public Dataset $dataset,
        public Field $field,
        public Aggregation $aggregation,
        public ?string $customLabel = null,
    ) {}

    /**
     * The stable key of the measure, e.g. "sum(sales.amount)".
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

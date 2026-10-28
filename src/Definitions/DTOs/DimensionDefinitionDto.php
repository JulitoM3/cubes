<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;

/**
 * A dimension placed on the rows or columns of a report.
 */
final readonly class DimensionDefinitionDto
{
    public function __construct(
        public Dataset $dataset,
        public Field $field,
    ) {}

    /**
     * The stable key of the dimension, e.g. "regions.name".
     */
    public function key(): string
    {
        return $this->dataset->reference($this->field);
    }

    public function label(): string
    {
        return $this->field->label;
    }
}

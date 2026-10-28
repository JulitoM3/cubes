<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Reports;

use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;

final readonly class Dimension
{
    public function __construct(
        public Dataset $dataset,
        public Field $field,
    ) {}

    public function key(): string
    {
        return $this->dataset->reference($this->field);
    }

    public function label(): string
    {
        return $this->field->label;
    }
}

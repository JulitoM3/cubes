<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\DTOs;

use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Datasets\Enums\FieldType;

/**
 * A field that rows and columns can be grouped by. It can only be counted when used as a value.
 */
final readonly class Dimension extends FieldDefinitionDto
{
    /**
     * @param  FieldFormat|null  $format  Null to take the format from the cast of the column.
     * @param  bool  $pivotable  Whether the frontend should offer the field as a column.
     * @param  string|null  $expression  Trusted SQL used instead of the column, e.g. "YEAR(sales.sold_at)".
     */
    public function __construct(
        string $column,
        ?string $label = null,
        ?FieldFormat $format = null,
        public bool $pivotable = false,
        ?string $expression = null,
    ) {
        parent::__construct($column, $label, $format, $expression);
    }

    public function type(): FieldType
    {
        return FieldType::Dimension;
    }

    public function withFormat(FieldFormat $format): static
    {
        return new self($this->column, $this->label, $format, $this->pivotable, $this->expression);
    }

    public function defaultFormat(): FieldFormat
    {
        return FieldFormat::Text;
    }
}

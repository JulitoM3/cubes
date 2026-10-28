<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

/**
 * What the user asked for: the base dataset and the fields placed on rows, columns, and values.
 */
final readonly class ReportDefinitionDto
{
    /**
     * @param  list<DimensionDefinitionDto>  $rows
     * @param  list<DimensionDefinitionDto>  $columns
     * @param  list<ValueDefinitionDto>  $values
     */
    public function __construct(
        public ?string $from,
        public array $rows,
        public array $columns,
        public array $values,
    ) {}

    /**
     * The row dimensions followed by the column dimensions, in select order.
     *
     * @return list<DimensionDefinitionDto>
     */
    public function dimensions(): array
    {
        return [...$this->rows, ...$this->columns];
    }

    public function isEmpty(): bool
    {
        return $this->rows === [] && $this->columns === [] && $this->values === [];
    }

    /**
     * The definition in the same shape the engine receives it.
     *
     * @return array{from: string|null, rows: list<array{key: string}>, columns: list<array{key: string}>, values: list<array{key: string, aggregation: string, label: string|null}>}
     */
    public function toArray(): array
    {
        $dimension = fn (DimensionDefinitionDto $dimension): array => ['key' => $dimension->key()];

        return [
            'from' => $this->from,
            'rows' => array_map($dimension, $this->rows),
            'columns' => array_map($dimension, $this->columns),
            'values' => array_map(fn (ValueDefinitionDto $value): array => [
                'key' => $value->dataset->reference($value->field),
                'aggregation' => $value->aggregation->value,
                'label' => $value->customLabel,
            ], $this->values),
        ];
    }
}

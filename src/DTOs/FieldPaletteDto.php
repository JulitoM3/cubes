<?php

declare(strict_types=1);

namespace Julitom3\Cubes\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\Aggregation;

/**
 * A field as the frontend palette shows it. Dropping it on rows, columns, or values sends its key back.
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class FieldPaletteDto implements Arrayable
{
    /**
     * @param  list<string>  $aggregations
     */
    public function __construct(
        public string $key,
        public string $dataset,
        public string $label,
        public string $type,
        public string $format,
        public bool $pivotable,
        public array $aggregations,
    ) {}

    public static function createFromField(DatasetContract $dataset, FieldDefinitionDto $field): self
    {
        return new self(
            key: $dataset->reference($field),
            dataset: $dataset->category(),
            label: $field->label,
            type: $field->type()->value,
            format: $field->format()->value,
            pivotable: $field instanceof Dimension && $field->pivotable,
            aggregations: $field->isDimension()
                ? []
                : array_map(fn (Aggregation $aggregation): string => $aggregation->value, $field->allowedAggregations()),
        );
    }

    /**
     * @return array{key: string, dataset: string, label: string, type: string, format: string, pivotable: bool, aggregations: list<string>}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'dataset' => $this->dataset,
            'label' => $this->label,
            'type' => $this->type,
            'format' => $this->format,
            'pivotable' => $this->pivotable,
            'aggregations' => $this->aggregations,
        ];
    }
}

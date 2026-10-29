<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Catalog;

use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\DTOs\FieldPaletteDto;
use Julitom3\Cubes\ReportEngine\DatasetRegistry;

/**
 * Describe the registered datasets so any frontend can build a field palette.
 */
final readonly class CatalogFactory
{
    public function __construct(private DatasetRegistry $registry) {}

    /**
     * @return list<array{name: string, label: string, fields: list<array{key: string, dataset: string, label: string, type: string, format: string, pivotable: bool, aggregations: list<string>}>}>
     */
    public function create(): array
    {
        return array_values(array_map(fn (DatasetContract $dataset): array => [
            'name' => $dataset->category(),
            'label' => $dataset->label(),
            'fields' => array_map(fn (FieldPaletteDto $field): array => $field->toArray(), $this->palette($dataset)),
        ], $this->registry->all()));
    }

    /**
     * The fields of a dataset as the frontend palette shows them.
     *
     * @return list<FieldPaletteDto>
     */
    public function palette(DatasetContract $dataset): array
    {
        return array_map(
            fn (FieldDefinitionDto $field): FieldPaletteDto => FieldPaletteDto::createFromField($dataset, $field),
            array_values($dataset->fieldMap()),
        );
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Resolvers;

use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\ReportEngine\DatasetRegistry;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ValueDefinitionDto;

/**
 * Resolve the fields a frontend sends, e.g. ["key" => "regions.name"], against the registered datasets.
 *
 * Only the key, the aggregation, and the label are read; any other attribute of a dropped
 * palette item is ignored, and a key that no dataset exposes is rejected.
 */
final readonly class FieldsResolver
{
    public function __construct(private DatasetRegistry $registry) {}

    /**
     * @param  array<array-key, mixed>  $fields
     * @return list<DimensionDefinitionDto>
     */
    public function dimensions(array $fields): array
    {
        $dimensions = [];

        foreach ($fields as $field) {
            $key = $this->string($field, 'key') ?? throw CubesException::invalidSelection();
            [$dataset, $definition] = $this->registry->resolve($key);

            if (! $definition->isDimension()) {
                throw CubesException::notADimension($key);
            }

            $dimensions[] = new DimensionDefinitionDto($dataset, $definition);
        }

        return $dimensions;
    }

    /**
     * @param  array<array-key, mixed>  $fields
     * @return list<ValueDefinitionDto>
     */
    public function values(array $fields): array
    {
        $values = [];

        foreach ($fields as $field) {
            $key = $this->string($field, 'key') ?? throw CubesException::invalidSelection();
            [$dataset, $definition] = $this->registry->resolve($key);

            $name = $this->string($field, 'aggregation');
            $aggregation = $name === null
                ? $definition->defaultAggregation()
                : Aggregation::fromName($name) ?? throw CubesException::invalidAggregation($key, $name);

            if (! $definition->allows($aggregation)) {
                throw CubesException::invalidAggregation($key, $aggregation->value);
            }

            $values[] = new ValueDefinitionDto($dataset, $definition, $aggregation, $this->string($field, 'label'));
        }

        return $values;
    }

    /**
     * Read an optional string attribute of a field, rejecting fields that are not arrays.
     */
    private function string(mixed $field, string $attribute): ?string
    {
        if (! is_array($field)) {
            throw CubesException::invalidSelection();
        }

        $value = $field[$attribute] ?? null;

        if ($value !== null && ! is_string($value)) {
            throw CubesException::invalidSelection();
        }

        return $value;
    }
}

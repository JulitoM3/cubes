<?php

declare(strict_types=1);

namespace Julitom3\Cubes;

use Illuminate\Contracts\Container\Container;
use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\DatasetRegistry;
use Julitom3\Cubes\Reports\Report;

/**
 * @phpstan-import-type ReportDefinition from Report
 */
class Cubes
{
    public function __construct(
        private readonly Container $container,
        private readonly DatasetRegistry $registry,
    ) {}

    /**
     * Start a new, independent report, optionally from a serialized definition.
     *
     * @param  ReportDefinition  $definition
     */
    public function report(array $definition = []): Report
    {
        return $this->container->make(Report::class)->fill($definition);
    }

    /**
     * @param  class-string<Dataset>|Dataset  $dataset
     */
    public function register(string|Dataset $dataset): Dataset
    {
        return $this->registry->register($dataset);
    }

    public function dataset(string $name): Dataset
    {
        return $this->registry->get($name);
    }

    /**
     * @return array<string, Dataset>
     */
    public function datasets(): array
    {
        return $this->registry->all();
    }

    /**
     * Describe every registered dataset and its fields, ready to send to a frontend.
     *
     * @return list<array{name: string, label: string, fields: list<array{key: string, column: string, label: string, type: string, format: string, pivotable: bool, aggregations: list<string>}>}>
     */
    public function catalog(): array
    {
        return array_values(array_map(fn (Dataset $dataset): array => $dataset->toArray(), $this->registry->all()));
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Julitom3\Cubes\Catalog\CatalogFactory;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\ReportEngine\DatasetRegistry;
use Julitom3\Cubes\ReportEngine\ReportEngine;

class Cubes
{
    public function __construct(
        private readonly Container $container,
        private readonly DatasetRegistry $registry,
        private readonly CatalogFactory $catalogFactory,
    ) {}

    /**
     * Start a new, independent report.
     */
    public function engine(): ReportEngine
    {
        return $this->container->make(ReportEngine::class);
    }

    /**
     * Register a Cubable model as a dataset at runtime.
     *
     * @param  class-string<Model>|Model  $model
     */
    public function register(string|Model $model): DatasetContract
    {
        return $this->registry->register($model);
    }

    public function dataset(string $name): DatasetContract
    {
        return $this->registry->get($name);
    }

    /**
     * @return array<string, DatasetContract>
     */
    public function datasets(): array
    {
        return $this->registry->all();
    }

    /**
     * Describe every registered dataset and its fields, ready to send to a frontend.
     *
     * @return list<array{name: string, label: string, fields: list<array{key: string, dataset: string, label: string, type: string, format: string, pivotable: bool, aggregations: list<string>}>}>
     */
    public function catalog(): array
    {
        return $this->catalogFactory->create();
    }
}

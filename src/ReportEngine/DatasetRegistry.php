<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine;

use Illuminate\Contracts\Container\Container;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Exceptions\CubesException;

final class DatasetRegistry
{
    /** @var array<string, DatasetContract> */
    private array $datasets = [];

    private int $version = 0;

    public function __construct(private readonly Container $container) {}

    /**
     * @param  class-string<DatasetContract>|DatasetContract  $dataset
     */
    public function register(string|DatasetContract $dataset): DatasetContract
    {
        if (is_string($dataset)) {
            $instance = $this->container->make($dataset);

            if (! $instance instanceof DatasetContract) {
                throw CubesException::invalidDataset($dataset);
            }

            $dataset = $instance;
        }

        $this->version++;

        return $this->datasets[$dataset->category()] = $dataset;
    }

    /**
     * A number that changes every time a dataset is registered, so derived data knows when to rebuild.
     */
    public function version(): int
    {
        return $this->version;
    }

    public function get(string $category): DatasetContract
    {
        return $this->datasets[$category] ?? throw CubesException::datasetNotFound($category);
    }

    public function has(string $category): bool
    {
        return isset($this->datasets[$category]);
    }

    /**
     * @return array<string, DatasetContract>
     */
    public function all(): array
    {
        return $this->datasets;
    }

    /**
     * Resolve a key such as "sales.amount" into its dataset and field.
     *
     * @return array{DatasetContract, FieldDefinitionDto}
     */
    public function resolve(string $key): array
    {
        $position = strrpos($key, '.');

        if ($position === false || $position === 0 || $position === strlen($key) - 1) {
            throw CubesException::invalidFieldReference($key);
        }

        $dataset = $this->get(substr($key, 0, $position));

        return [$dataset, $dataset->getField(substr($key, $position + 1))];
    }
}

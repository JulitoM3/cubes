<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets;

use Illuminate\Contracts\Container\Container;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Graph\DatasetGraph;

final class DatasetRegistry
{
    /** @var array<string, Dataset> */
    private array $datasets = [];

    private ?DatasetGraph $graph = null;

    public function __construct(private readonly Container $container) {}

    /**
     * @param  class-string<Dataset>|Dataset  $dataset
     */
    public function register(string|Dataset $dataset): Dataset
    {
        if (is_string($dataset)) {
            $instance = $this->container->make($dataset);

            if (! $instance instanceof Dataset) {
                throw CubesException::invalidDataset($dataset);
            }

            $dataset = $instance;
        }

        $this->graph = null;

        return $this->datasets[$dataset->name()] = $dataset;
    }

    public function get(string $name): Dataset
    {
        return $this->datasets[$name] ?? throw CubesException::datasetNotFound($name);
    }

    public function has(string $name): bool
    {
        return isset($this->datasets[$name]);
    }

    public function forTable(string $table): ?Dataset
    {
        foreach ($this->datasets as $dataset) {
            if ($dataset->table() === $table) {
                return $dataset;
            }
        }

        return null;
    }

    /**
     * @return array<string, Dataset>
     */
    public function all(): array
    {
        return $this->datasets;
    }

    /**
     * Resolve a reference such as "sales.amount" into its dataset and field.
     *
     * @return array{Dataset, Field}
     */
    public function resolve(string $reference): array
    {
        $position = strrpos($reference, '.');

        if ($position === false || $position === 0 || $position === strlen($reference) - 1) {
            throw CubesException::invalidFieldReference($reference);
        }

        $dataset = $this->get(substr($reference, 0, $position));

        return [$dataset, $dataset->field(substr($reference, $position + 1))];
    }

    public function graph(): DatasetGraph
    {
        return $this->graph ??= new DatasetGraph($this);
    }
}

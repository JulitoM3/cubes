<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Reports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Julitom3\Cubes\Datasets\DatasetRegistry;
use Julitom3\Cubes\Enums\Aggregation;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Matrix\Matrix;
use Julitom3\Cubes\Matrix\MatrixBuilder;

/**
 * @phpstan-type ValueDefinition array{field: string, aggregation?: string|null, label?: string|null}
 * @phpstan-type ReportDefinition array{from?: string|null, rows?: list<string>, columns?: list<string>, values?: list<string|ValueDefinition>}
 */
final class Report
{
    private ?string $root = null;

    /** @var list<Dimension> */
    private array $rows = [];

    /** @var list<Dimension> */
    private array $columns = [];

    /** @var list<Measure> */
    private array $values = [];

    public function __construct(
        private readonly DatasetRegistry $registry,
        private readonly QueryCompiler $compiler,
        private readonly MatrixBuilder $matrixBuilder,
    ) {}

    /**
     * Use the given dataset as the base table instead of resolving it automatically.
     */
    public function from(string $dataset): self
    {
        $this->root = $this->registry->get($dataset)->name();

        return $this;
    }

    /**
     * Group the report rows by the given fields, e.g. "regions.name".
     */
    public function rows(string ...$fields): self
    {
        foreach ($fields as $field) {
            $this->rows[] = $this->dimension($field);
        }

        return $this;
    }

    /**
     * Pivot the report columns by the given fields.
     */
    public function columns(string ...$fields): self
    {
        foreach ($fields as $field) {
            $this->columns[] = $this->dimension($field);
        }

        return $this;
    }

    /**
     * Aggregate the given field, e.g. value("sales.amount", "sum", "Total sales").
     */
    public function value(string $field, Aggregation|string|null $aggregation = null, ?string $label = null): self
    {
        [$dataset, $definition] = $this->registry->resolve($field);

        if (is_string($aggregation)) {
            $aggregation = Aggregation::fromName($aggregation)
                ?? throw CubesException::invalidAggregation($field, $aggregation);
        }

        $aggregation ??= $definition->defaultAggregation();

        if (! $definition->allows($aggregation)) {
            throw CubesException::invalidAggregation($field, $aggregation->value);
        }

        $this->values[] = new Measure($dataset, $definition, $aggregation, $label);

        return $this;
    }

    /**
     * Add the selections of a serialized definition, as sent by a frontend.
     *
     * @param  ReportDefinition  $definition
     */
    public function fill(array $definition): self
    {
        if (isset($definition['from'])) {
            $this->from($definition['from']);
        }

        $this->rows(...$definition['rows'] ?? []);
        $this->columns(...$definition['columns'] ?? []);

        foreach ($definition['values'] ?? [] as $value) {
            is_string($value)
                ? $this->value($value)
                : $this->value($value['field'], $value['aggregation'] ?? null, $value['label'] ?? null);
        }

        return $this;
    }

    /**
     * @return array{from: string|null, rows: list<string>, columns: list<string>, values: list<array{field: string, aggregation: string, label: string|null}>}
     */
    public function toArray(): array
    {
        return [
            'from' => $this->root,
            'rows' => array_map(fn (Dimension $dimension): string => $dimension->key(), $this->rows),
            'columns' => array_map(fn (Dimension $dimension): string => $dimension->key(), $this->columns),
            'values' => array_map(fn (Measure $measure): array => [
                'field' => $measure->dataset->reference($measure->field),
                'aggregation' => $measure->aggregation->value,
                'label' => $measure->customLabel,
            ], $this->values),
        ];
    }

    public function root(): ?string
    {
        return $this->root;
    }

    /** @return list<Dimension> */
    public function rowDimensions(): array
    {
        return $this->rows;
    }

    /** @return list<Dimension> */
    public function columnDimensions(): array
    {
        return $this->columns;
    }

    /** @return list<Measure> */
    public function measures(): array
    {
        return $this->values;
    }

    public function toQuery(): Builder
    {
        return $this->compiler->compile($this);
    }

    /**
     * The flat result, keyed by field and measure keys, e.g. ["regions.name" => "North", "sum(sales.amount)" => 10].
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function get(): Collection
    {
        $dimensions = [...$this->rows, ...$this->columns];

        return $this->records()->map(function (array $record) use ($dimensions): array {
            $row = [];

            foreach ($dimensions as $position => $dimension) {
                $row[$dimension->key()] = $record[QueryCompiler::dimensionAlias($position)] ?? null;
            }

            foreach ($this->values as $position => $measure) {
                $row[$measure->key()] = $record[QueryCompiler::measureAlias($position)] ?? null;
            }

            return $row;
        });
    }

    /**
     * The pivot table, ready to render with any frontend.
     */
    public function matrix(): Matrix
    {
        return $this->matrixBuilder->build($this, $this->records());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function records(): Collection
    {
        return $this->toQuery()
            ->get()
            ->map($this->recordToArray(...))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function recordToArray(object $record): array
    {
        return get_object_vars($record);
    }

    private function dimension(string $field): Dimension
    {
        [$dataset, $definition] = $this->registry->resolve($field);

        if (! $definition->isDimension()) {
            throw CubesException::notADimension($field);
        }

        return new Dimension($dataset, $definition);
    }
}

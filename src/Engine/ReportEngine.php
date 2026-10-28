<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Engine;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Julitom3\Cubes\Compilers\SqlCompiler;
use Julitom3\Cubes\Datasets\DatasetRegistry;
use Julitom3\Cubes\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\ValueDefinitionDto;
use Julitom3\Cubes\Definitions\QueryDefinition;
use Julitom3\Cubes\Matrix\Matrix;
use Julitom3\Cubes\Matrix\MatrixBuilder;
use Julitom3\Cubes\Resolvers\FieldsResolver;

/**
 * Build one report from the fields a user drags onto rows, columns, and values.
 *
 * Every engine holds the state of a single report; resolve a new one for each report.
 */
final class ReportEngine
{
    private ?string $from = null;

    /** @var list<DimensionDefinitionDto> */
    private array $rows = [];

    /** @var list<DimensionDefinitionDto> */
    private array $columns = [];

    /** @var list<ValueDefinitionDto> */
    private array $values = [];

    public function __construct(
        private readonly DatasetRegistry $registry,
        private readonly FieldsResolver $fieldsResolver,
        private readonly QueryDefinition $queryDefinition,
        private readonly SqlCompiler $sqlCompiler,
        private readonly MatrixBuilder $matrixBuilder,
    ) {}

    /**
     * Use the given dataset as the base table instead of resolving it automatically.
     */
    public function from(?string $dataset): self
    {
        $this->from = $dataset === null ? null : $this->registry->get($dataset)->name();

        return $this;
    }

    /**
     * Group the rows by the given fields, e.g. [["key" => "regions.name"]].
     *
     * @param  array<array-key, mixed>  $rows
     */
    public function rows(array $rows): self
    {
        $this->rows = [...$this->rows, ...$this->fieldsResolver->dimensions($rows)];

        return $this;
    }

    /**
     * Pivot the columns by the given fields, e.g. [["key" => "sales.year"]].
     *
     * @param  array<array-key, mixed>  $columns
     */
    public function columns(array $columns): self
    {
        $this->columns = [...$this->columns, ...$this->fieldsResolver->dimensions($columns)];

        return $this;
    }

    /**
     * Aggregate the given fields, e.g. [["key" => "sales.amount", "aggregation" => "sum", "label" => "Total"]].
     *
     * @param  array<array-key, mixed>  $values
     */
    public function values(array $values): self
    {
        $this->values = [...$this->values, ...$this->fieldsResolver->values($values)];

        return $this;
    }

    public function reportDefinition(): ReportDefinitionDto
    {
        return new ReportDefinitionDto($this->from, $this->rows, $this->columns, $this->values);
    }

    public function queryDefinition(): QueryDefinitionDto
    {
        return $this->queryDefinition->create($this->reportDefinition());
    }

    public function build(): Builder
    {
        return $this->sqlCompiler->compile($this->queryDefinition());
    }

    /**
     * The flat result, keyed by field and value keys, e.g. ["regions.name" => "North", "sum(sales.amount)" => 10].
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function get(): Collection
    {
        $dimensions = $this->reportDefinition()->dimensions();

        return $this->records()->map(function (array $record) use ($dimensions): array {
            $row = [];

            foreach ($dimensions as $position => $dimension) {
                $row[$dimension->key()] = $record[QueryDefinition::dimensionAlias($position)] ?? null;
            }

            foreach ($this->values as $position => $value) {
                $row[$value->key()] = $record[QueryDefinition::valueAlias($position)] ?? null;
            }

            return $row;
        });
    }

    /**
     * The pivot table, ready to render with any frontend.
     */
    public function matrixRender(): Matrix
    {
        return $this->matrixBuilder->build($this->reportDefinition(), $this->records());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function records(): Collection
    {
        return $this->build()
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
}

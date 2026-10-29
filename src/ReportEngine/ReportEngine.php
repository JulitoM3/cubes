<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixRenderDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\MatrixDefinition;
use Julitom3\Cubes\ReportEngine\Definitions\QueryDefinition;
use Julitom3\Cubes\ReportEngine\Definitions\ReportDefinition;
use Julitom3\Cubes\ReportEngine\Matrix\MatrixRenderFactory;
use Julitom3\Cubes\ReportEngine\Matrix\MatrixTreeFactory;
use Julitom3\Cubes\ReportEngine\Resolvers\FieldsResolver;
use Julitom3\Cubes\ReportEngine\SqlCompiler\SqlCompiler;

/**
 * The fluent entry point of a report: it only delegates to the collaborators of each step.
 *
 * Every engine holds the state of a single report; resolve a new one for each report.
 */
final readonly class ReportEngine
{
    public function __construct(
        private DatasetRegistry $registry,
        private FieldsResolver $fieldsResolver,
        private ReportDefinition $reportDefinition,
        private QueryDefinition $queryDefinition,
        private SqlCompiler $sqlCompiler,
        private MatrixDefinition $matrixDefinition,
        private MatrixTreeFactory $matrixTreeFactory,
        private MatrixRenderFactory $matrixRenderFactory,
    ) {}

    /**
     * Use the given dataset as the base table instead of resolving it automatically.
     */
    public function from(?string $dataset): self
    {
        $this->reportDefinition->setFrom($dataset === null ? null : $this->registry->get($dataset));

        return $this;
    }

    /**
     * Group the rows by the given fields, e.g. [["key" => "regions.name"]].
     *
     * @param  array<array-key, mixed>  $rows
     */
    public function rows(array $rows): self
    {
        $this->reportDefinition->addRows(...$this->fieldsResolver->dimensions($rows));

        return $this;
    }

    /**
     * Pivot the columns by the given fields, e.g. [["key" => "sales.year"]].
     *
     * @param  array<array-key, mixed>  $columns
     */
    public function columns(array $columns): self
    {
        $this->reportDefinition->addColumns(...$this->fieldsResolver->dimensions($columns));

        return $this;
    }

    /**
     * Aggregate the given fields, e.g. [["key" => "sales.amount", "aggregation" => "sum", "label" => "Total"]].
     *
     * @param  array<array-key, mixed>  $values
     */
    public function values(array $values): self
    {
        $this->reportDefinition->addValues(...$this->fieldsResolver->values($values));

        return $this;
    }

    public function reportDefinition(): ReportDefinitionDto
    {
        return $this->reportDefinition->create();
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
        $queryDefinition = $this->queryDefinition();

        return $this->records($queryDefinition)->map(function (array $record) use ($queryDefinition): array {
            $row = [];

            foreach ($queryDefinition->selects as $select) {
                $row[$select->origin->key()] = $record[$select->alias] ?? null;
            }

            return $row;
        });
    }

    /**
     * The pivot table, ready to render with any frontend.
     */
    public function matrixRender(): MatrixRenderDto
    {
        $reportDefinition = $this->reportDefinition();
        $queryDefinition = $this->queryDefinition->create($reportDefinition);

        $layout = $this->matrixTreeFactory->create(
            $this->matrixDefinition->create($reportDefinition, $queryDefinition),
            $this->records($queryDefinition),
        );

        return $this->matrixRenderFactory->create($layout);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function records(QueryDefinitionDto $queryDefinition): Collection
    {
        return $this->sqlCompiler->compile($queryDefinition)
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

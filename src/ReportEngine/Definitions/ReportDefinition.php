<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions;

use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ValueDefinitionDto;

/**
 * Collect the resolved rows, columns, and values of one report.
 *
 * The container resolves a new instance for every engine, so state never leaks between reports.
 */
final class ReportDefinition
{
    private ?DatasetContract $from = null;

    /** @var list<DimensionDefinitionDto> */
    private array $rows = [];

    /** @var list<DimensionDefinitionDto> */
    private array $columns = [];

    /** @var list<ValueDefinitionDto> */
    private array $values = [];

    public function setFrom(?DatasetContract $dataset): void
    {
        $this->from = $dataset;
    }

    public function addRows(DimensionDefinitionDto ...$rows): void
    {
        $this->rows = [...$this->rows, ...array_values($rows)];
    }

    public function addColumns(DimensionDefinitionDto ...$columns): void
    {
        $this->columns = [...$this->columns, ...array_values($columns)];
    }

    public function addValues(ValueDefinitionDto ...$values): void
    {
        $this->values = [...$this->values, ...array_values($values)];
    }

    public function create(): ReportDefinitionDto
    {
        return new ReportDefinitionDto(
            from: $this->from,
            rows: $this->rows,
            columns: $this->columns,
            values: $this->values,
        );
    }
}

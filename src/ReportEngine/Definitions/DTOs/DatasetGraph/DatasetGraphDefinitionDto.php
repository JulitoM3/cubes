<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph;

use Julitom3\Cubes\Exceptions\CubesException;

/**
 * The graph of registered datasets, keyed by table.
 */
final readonly class DatasetGraphDefinitionDto
{
    /**
     * @param  array<string, DatasetNodeDto>  $nodes
     */
    public function __construct(
        public array $nodes = [],
    ) {}

    public function getNodeByTable(string $table): DatasetNodeDto
    {
        return $this->nodes[$table] ?? throw CubesException::datasetNotFound($table);
    }
}

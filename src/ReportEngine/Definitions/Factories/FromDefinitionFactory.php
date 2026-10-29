<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\Factories;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetRootDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\FromDefinitionDto;

/**
 * Start the query from the table of the root node.
 */
final readonly class FromDefinitionFactory
{
    public function createDefinition(DatasetRootDto $datasetRoot): FromDefinitionDto
    {
        return new FromDefinitionDto($datasetRoot->root->table);
    }
}

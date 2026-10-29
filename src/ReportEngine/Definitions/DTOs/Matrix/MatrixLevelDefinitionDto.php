<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DimensionDefinitionDto;

/**
 * One level of an axis: the dimension shown at that depth and the alias it is selected as.
 */
final readonly class MatrixLevelDefinitionDto
{
    public function __construct(
        public DimensionDefinitionDto $field,
        public int $depth,
        public string $alias,
    ) {}
}

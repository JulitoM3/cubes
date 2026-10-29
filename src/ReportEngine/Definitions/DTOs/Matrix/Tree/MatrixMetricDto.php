<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Tree;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixValueDefinitionDto;

/**
 * The value of a metric for one row leaf and one column leaf.
 */
final readonly class MatrixMetricDto
{
    public function __construct(
        public MatrixValueDefinitionDto $definition,
        public mixed $value,
    ) {}
}

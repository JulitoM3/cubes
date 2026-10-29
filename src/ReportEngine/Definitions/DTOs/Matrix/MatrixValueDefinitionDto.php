<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\ValueDefinitionDto;

/**
 * A value of the matrix and the alias it is selected as.
 */
final readonly class MatrixValueDefinitionDto
{
    public function __construct(
        public ValueDefinitionDto $value,
        public string $alias,
    ) {}
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

final readonly class MatrixAxisDefinitionDto
{
    /**
     * @param  list<MatrixLevelDefinitionDto>  $levels
     */
    public function __construct(
        public array $levels,
    ) {}

    public function isEmpty(): bool
    {
        return $this->levels === [];
    }

    public function depth(): int
    {
        return count($this->levels);
    }
}

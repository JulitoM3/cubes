<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

final readonly class MatrixDefinitionDto
{
    /**
     * @param  list<MatrixValueDefinitionDto>  $values
     */
    public function __construct(
        public MatrixAxisDefinitionDto $rows,
        public MatrixAxisDefinitionDto $columns,
        public array $values,
    ) {}

    /**
     * Whether the header needs a last row with the value labels: with several values, or with values and no columns.
     */
    public function showsValueHeaders(): bool
    {
        return count($this->values) > 1 || ($this->columns->isEmpty() && $this->values !== []);
    }

    /**
     * The number of header rows: one per column level, plus the value labels when shown.
     */
    public function headerDepth(): int
    {
        return max(1, $this->columns->depth() + ($this->showsValueHeaders() ? 1 : 0));
    }
}

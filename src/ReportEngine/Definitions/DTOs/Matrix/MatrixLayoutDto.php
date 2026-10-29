<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Nodes\MatrixNodeDto;

final readonly class MatrixLayoutDto
{
    public function __construct(
        public MatrixDefinitionDto $definition,
        public MatrixNodeDto $rowTree,
        public MatrixNodeDto $columnTree,
    ) {}

    /**
     * The column leaves the body cells and value headers follow.
     *
     * Without column dimensions the root is the only leaf; with column dimensions and no
     * records there are no leaves at all.
     *
     * @return list<MatrixNodeDto>
     */
    public function columnLeaves(): array
    {
        if ($this->definition->columns->isEmpty() || $this->columnTree->hasChildren()) {
            return $this->columnTree->leaves();
        }

        return [];
    }
}

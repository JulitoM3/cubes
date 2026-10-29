<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

/**
 * A header cell: the label of a row dimension, a column value, or a value.
 */
final readonly class MatrixHeaderCellDto
{
    public const string DIMENSION = 'dimension';

    public const string COLUMN = 'column';

    public const string MEASURE = 'measure';

    public function __construct(
        public string $label,
        public string $kind,
        public int $depth,
        public int $colspan = 1,
        public int $rowspan = 1,
        public bool $isLeaf = false,
        public mixed $value = null,
    ) {}

    /**
     * @return array{label: string, kind: string, depth: int, colspan: int, rowspan: int, isLeaf: bool, value: mixed}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'kind' => $this->kind,
            'depth' => $this->depth,
            'colspan' => $this->colspan,
            'rowspan' => $this->rowspan,
            'isLeaf' => $this->isLeaf,
            'value' => $this->value,
        ];
    }
}

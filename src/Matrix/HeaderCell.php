<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Matrix;

final readonly class HeaderCell
{
    public const string DIMENSION = 'dimension';

    public const string COLUMN = 'column';

    public const string MEASURE = 'measure';

    /**
     * @param  string  $kind  One of the DIMENSION, COLUMN or MEASURE constants.
     */
    public function __construct(
        public string $label,
        public string $kind,
        public int $colspan = 1,
        public int $rowspan = 1,
        public mixed $value = null,
    ) {}

    /**
     * @return array{label: string, kind: string, colspan: int, rowspan: int, value: mixed}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'kind' => $this->kind,
            'colspan' => $this->colspan,
            'rowspan' => $this->rowspan,
            'value' => $this->value,
        ];
    }
}

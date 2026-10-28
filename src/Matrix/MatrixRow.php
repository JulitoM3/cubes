<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Matrix;

final readonly class MatrixRow
{
    /**
     * @param  list<array{label: string, value: mixed}>  $headers  One entry per row dimension.
     * @param  list<array{value: mixed, format: string}>  $cells  One entry per column and value, aligned with the header leaves.
     */
    public function __construct(
        public array $headers,
        public array $cells,
    ) {}

    /**
     * @return array{headers: list<array{label: string, value: mixed}>, cells: list<array{value: mixed, format: string}>}
     */
    public function toArray(): array
    {
        return [
            'headers' => $this->headers,
            'cells' => $this->cells,
        ];
    }
}

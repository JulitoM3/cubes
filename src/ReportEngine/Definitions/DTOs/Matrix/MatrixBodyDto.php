<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

final readonly class MatrixBodyDto
{
    /**
     * @param  list<MatrixBodyRowDto>  $rows
     */
    public function __construct(
        public array $rows = [],
    ) {}

    /**
     * @return array{rows: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'rows' => array_map(fn (MatrixBodyRowDto $row): array => $row->toArray(), $this->rows),
        ];
    }
}

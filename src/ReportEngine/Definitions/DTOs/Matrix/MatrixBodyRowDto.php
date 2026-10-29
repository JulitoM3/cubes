<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

/**
 * A body row: the values of its row dimensions and one cell per column leaf and value.
 */
final readonly class MatrixBodyRowDto
{
    /**
     * @param  list<MatrixHeaderCellDto>  $headers
     * @param  list<MatrixValueCellDto>  $cells
     */
    public function __construct(
        public array $headers,
        public array $cells,
    ) {}

    /**
     * @return array{headers: list<array<string, mixed>>, cells: list<array{value: mixed, format: string}>}
     */
    public function toArray(): array
    {
        return [
            'headers' => array_map(fn (MatrixHeaderCellDto $cell): array => $cell->toArray(), $this->headers),
            'cells' => array_map(fn (MatrixValueCellDto $cell): array => $cell->toArray(), $this->cells),
        ];
    }
}

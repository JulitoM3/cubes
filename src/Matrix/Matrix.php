<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Matrix;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class Matrix implements Arrayable, JsonSerializable
{
    /**
     * @param  list<list<HeaderCell>>  $headers  The header rows, from top to bottom.
     * @param  list<MatrixRow>  $rows  The body rows.
     */
    public function __construct(
        public array $headers,
        public array $rows,
    ) {}

    /**
     * @return array{headers: list<list<array{label: string, kind: string, colspan: int, rowspan: int, value: mixed}>>, rows: list<array{headers: list<array{label: string, value: mixed}>, cells: list<array{value: mixed, format: string}>}>}
     */
    public function toArray(): array
    {
        return [
            'headers' => array_map(
                fn (array $row): array => array_map(fn (HeaderCell $cell): array => $cell->toArray(), $row),
                $this->headers,
            ),
            'rows' => array_map(fn (MatrixRow $row): array => $row->toArray(), $this->rows),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

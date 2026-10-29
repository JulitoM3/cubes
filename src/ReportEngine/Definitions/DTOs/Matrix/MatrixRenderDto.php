<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * The rendered matrix. It serializes to plain arrays, so any frontend can draw it as a table.
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class MatrixRenderDto implements Arrayable, JsonSerializable
{
    /**
     * @param  list<list<MatrixHeaderCellDto>>  $headers  One list of cells per header row.
     */
    public function __construct(
        public array $headers,
        public MatrixBodyDto $body,
    ) {}

    /**
     * @return array{headers: list<list<array<string, mixed>>>, body: array{rows: list<array<string, mixed>>}}
     */
    public function toArray(): array
    {
        return [
            'headers' => array_map(
                fn (array $row): array => array_map(fn (MatrixHeaderCellDto $cell): array => $cell->toArray(), $row),
                $this->headers,
            ),
            'body' => $this->body->toArray(),
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

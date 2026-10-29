<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix;

/**
 * A body cell: the value of a metric and the format of its field.
 */
final readonly class MatrixValueCellDto
{
    public function __construct(
        public mixed $value,
        public string $format,
    ) {}

    /**
     * @return array{value: mixed, format: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'format' => $this->format,
        ];
    }
}

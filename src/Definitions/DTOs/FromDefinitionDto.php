<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

use Julitom3\Cubes\Datasets\Dataset;

/**
 * The FROM clause: the base table every join starts from.
 */
final readonly class FromDefinitionDto
{
    public function __construct(
        public string $table,
    ) {}

    public static function createFromDataset(Dataset $dataset): self
    {
        return new self($dataset->table());
    }
}

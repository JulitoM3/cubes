<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs;

/**
 * The FROM clause: the base table every join starts from.
 */
final readonly class FromDefinitionDto
{
    public function __construct(
        public string $table,
    ) {}
}

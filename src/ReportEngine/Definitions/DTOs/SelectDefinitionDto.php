<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs;

use Julitom3\Cubes\Datasets\Enums\Aggregation;

/**
 * One column of the SELECT clause. Every select is aliased, so results never depend on column names.
 */
final readonly class SelectDefinitionDto
{
    /**
     * @param  DimensionDefinitionDto|ValueDefinitionDto  $origin  The selected field this column comes from.
     */
    public function __construct(
        public DimensionDefinitionDto|ValueDefinitionDto $origin,
        public string $table,
        public string $column,
        public ?string $expression,
        public ?Aggregation $aggregation,
        public string $alias,
    ) {}
}

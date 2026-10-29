<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs;

/**
 * One column of the ORDER BY clause.
 */
final readonly class OrderByDefinitionDto
{
    /**
     * @param  'asc'|'desc'  $direction
     */
    public function __construct(
        public string $table,
        public string $column,
        public ?string $expression,
        public string $direction = 'asc',
    ) {}
}

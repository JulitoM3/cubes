<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs;

/**
 * One column of the GROUP BY clause, one per dimension of the report.
 */
final readonly class GroupByDefinitionDto
{
    public function __construct(
        public string $table,
        public string $column,
        public ?string $expression,
    ) {}
}

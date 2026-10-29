<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Compilers\Contracts;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;

/**
 * Compile one clause of a query definition onto the query builder.
 */
interface CompilerContract
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void;
}

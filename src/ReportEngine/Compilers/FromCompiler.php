<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\ReportEngine\Compilers\Contracts\CompilerContract;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;

final readonly class FromCompiler implements CompilerContract
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        $builder->from($query->from->table);
    }
}

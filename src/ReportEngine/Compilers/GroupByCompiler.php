<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\ReportEngine\Compilers\Contracts\CompilerContract;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;

final readonly class GroupByCompiler implements CompilerContract
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        foreach ($query->groupBy as $group) {
            $builder->groupBy(new SqlExpression($group->table, $group->column, $group->expression));
        }
    }
}

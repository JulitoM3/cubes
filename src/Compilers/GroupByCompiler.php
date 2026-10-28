<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\Compilers\Contracts\ClauseCompiler;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;

final readonly class GroupByCompiler implements ClauseCompiler
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        foreach ($query->groupBy as $group) {
            $builder->groupBy(new SqlExpression($group->table, $group->column, $group->expression));
        }
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\Compilers\Contracts\ClauseCompiler;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;

final readonly class SelectCompiler implements ClauseCompiler
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        foreach ($query->selects as $select) {
            $builder->addSelect(new SqlExpression(
                $select->table,
                $select->column,
                $select->expression,
                $select->aggregation,
                $select->alias,
            ));
        }
    }
}

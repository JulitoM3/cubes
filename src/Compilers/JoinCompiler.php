<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\Compilers\Contracts\ClauseCompiler;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;

final readonly class JoinCompiler implements ClauseCompiler
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        foreach ($query->joins as $join) {
            $builder->join(
                $join->table,
                "{$join->from}.{$join->localKey}",
                '=',
                "{$join->table}.{$join->foreignKey}",
                $join->type->value,
            );
        }
    }
}

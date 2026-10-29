<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\ReportEngine\Compilers\Contracts\CompilerContract;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;

final readonly class JoinCompiler implements CompilerContract
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        foreach ($query->joins as $join) {
            $builder->join(
                $join->table,
                "{$join->from}.{$join->localKey}",
                '=',
                "{$join->table}.{$join->foreignKey}",
                $join->joinType->value,
            );
        }
    }
}

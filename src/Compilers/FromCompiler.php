<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\Compilers\Contracts\ClauseCompiler;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;

final readonly class FromCompiler implements ClauseCompiler
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        $builder->from($query->from->table);
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\Compilers\Contracts\ClauseCompiler;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;

final readonly class OrderByCompiler implements ClauseCompiler
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        foreach ($query->orders as $order) {
            $builder->orderBy(new SqlExpression($order->table, $order->column, $order->expression), $order->direction);
        }
    }
}

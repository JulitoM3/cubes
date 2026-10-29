<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Compilers;

use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\ReportEngine\Compilers\Contracts\CompilerContract;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;

final readonly class OrderByCompiler implements CompilerContract
{
    public function compile(Builder $builder, QueryDefinitionDto $query): void
    {
        foreach ($query->orders as $order) {
            $builder->orderBy(new SqlExpression($order->table, $order->column, $order->expression), $order->direction);
        }
    }
}

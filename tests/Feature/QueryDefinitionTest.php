<?php

declare(strict_types=1);

use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\JoinType;
use Julitom3\Cubes\Facades\Cubes;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\FromDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\GroupByDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\JoinDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\OrderByDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\SelectDefinitionDto;

it('describes every clause of the query before compiling it', function () {
    $query = Cubes::engine()
        ->rows([['key' => 'regions.name']])
        ->columns([['key' => 'sales.short_year']])
        ->values([['key' => 'sales.amount', 'aggregation' => 'avg', 'label' => 'Average']])
        ->queryDefinition();

    expect($query->from)->toEqual(new FromDefinitionDto('sales'))
        ->and($query->joins)->toEqual([
            new JoinDefinitionDto('stores', 'sales', 'store_id', 'id', JoinType::Left),
            new JoinDefinitionDto('regions', 'stores', 'region_id', 'id', JoinType::Left),
        ])
        ->and(array_map(fn (SelectDefinitionDto $select): array => [
            $select->origin->key(), $select->table, $select->column, $select->expression, $select->aggregation, $select->alias,
        ], $query->selects))->toBe([
            ['regions.name', 'regions', 'name', null, null, 'd0'],
            ['sales.short_year', 'sales', 'short_year', 'sales.year - 2000', null, 'd1'],
            ['avg(sales.amount)', 'sales', 'amount', null, Aggregation::Avg, 'v0'],
        ])
        ->and($query->groupBy)->toEqual([
            new GroupByDefinitionDto('regions', 'name', null),
            new GroupByDefinitionDto('sales', 'short_year', 'sales.year - 2000'),
        ])
        ->and($query->orders)->toEqual([
            new OrderByDefinitionDto('regions', 'name', null),
            new OrderByDefinitionDto('sales', 'short_year', 'sales.year - 2000'),
        ]);
});

it('is the only source of the select aliases, even for a field selected twice', function () {
    $engine = Cubes::engine()->values([
        ['key' => 'sales.amount', 'aggregation' => 'sum'],
        ['key' => 'sales.amount', 'aggregation' => 'max'],
    ]);

    $query = $engine->queryDefinition();
    $values = $query->selects;

    expect($query->aliasOf($values[0]->origin))->toBe('v0')
        ->and($query->aliasOf($values[1]->origin))->toBe('v1')
        ->and($engine->get()->all())->toEqual([['sum(sales.amount)' => 390, 'max(sales.amount)' => 200]]);
});

it('compiles each clause of the query definition', function () {
    $sql = Cubes::engine()
        ->rows([['key' => 'regions.name']])
        ->values([['key' => 'sales.amount', 'aggregation' => 'avg', 'label' => 'Average']])
        ->build()
        ->toSql();

    expect($sql)->toBe(
        'select "regions"."name" as "d0", AVG("sales"."amount") as "v0" from "sales"'
        .' left join "stores" on "sales"."store_id" = "stores"."id"'
        .' left join "regions" on "stores"."region_id" = "regions"."id"'
        .' group by "regions"."name" order by "regions"."name" asc',
    );
});

it('never compiles the custom label', function () {
    $sql = Cubes::engine()
        ->values([['key' => 'sales.amount', 'label' => "Total'); drop table sales; --"]])
        ->build()
        ->toSql();

    expect($sql)->not->toContain('drop table');
});

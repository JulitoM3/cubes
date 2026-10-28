<?php

declare(strict_types=1);

use Julitom3\Cubes\Definitions\DTOs\FromDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\GroupByDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\JoinDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\OrderByDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\SelectDefinitionDto;
use Julitom3\Cubes\Enums\Aggregation;
use Julitom3\Cubes\Enums\JoinType;
use Julitom3\Cubes\Facades\Cubes;

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
        ->and($query->selects)->toEqual([
            new SelectDefinitionDto('regions', 'name', null, null, 'd0'),
            new SelectDefinitionDto('sales', 'short_year', 'sales.year - 2000', null, 'd1'),
            new SelectDefinitionDto('sales', 'amount', null, Aggregation::Avg, 'v0'),
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

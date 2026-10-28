<?php

declare(strict_types=1);

use Julitom3\Cubes\Enums\Aggregation;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Facades\Cubes;

it('aggregates a value grouped by a related dataset, joining through intermediate tables', function () {
    $rows = Cubes::report()
        ->rows('regions.name')
        ->value('sales.amount', 'sum')
        ->get();

    expect($rows->all())->toEqual([
        ['regions.name' => null, 'sum(sales.amount)' => 10],
        ['regions.name' => 'North', 'sum(sales.amount)' => 180],
        ['regions.name' => 'South', 'sum(sales.amount)' => 200],
    ]);
});

it('uses the dataset of the first value as the base table', function () {
    $sql = Cubes::report()->rows('regions.name')->value('sales.amount')->toQuery()->toSql();

    expect($sql)->toContain('from "sales"')
        ->and($sql)->toContain('left join "stores" on "sales"."store_id" = "stores"."id"')
        ->and($sql)->toContain('left join "regions" on "stores"."region_id" = "regions"."id"');
});

it('joins each table only once when several fields share the path', function () {
    $sql = Cubes::report()
        ->rows('regions.name', 'stores.name')
        ->value('sales.amount')
        ->toQuery()
        ->toSql();

    expect(substr_count($sql, 'join "stores"'))->toBe(1)
        ->and(substr_count($sql, 'join "regions"'))->toBe(1);
});

it('walks relations in reverse when the base table is set explicitly', function () {
    $rows = Cubes::report()
        ->from('regions')
        ->rows('regions.name')
        ->value('sales.id', Aggregation::Count)
        ->get();

    expect($rows->mapWithKeys(fn (array $row): array => [$row['regions.name'] => $row['count(sales.id)']])->all())
        ->toEqual(['North' => 3, 'South' => 1, 'West' => 0]);
});

it('selects field expressions', function () {
    $rows = Cubes::report()->rows('sales.short_year')->value('sales.amount')->get();

    expect($rows->mapWithKeys(fn (array $row): array => [$row['sales.short_year'] => $row['sum(sales.amount)']])->all())
        ->toEqual([24 => 140, 25 => 250]);
});

it('uses the first allowed aggregation by default', function () {
    $report = Cubes::report()->value('sales.amount')->value('sales.id');

    expect(array_column($report->toArray()['values'], 'aggregation'))->toBe(['sum', 'count']);
});

it('keeps every report independent', function () {
    Cubes::report()->rows('regions.name')->value('sales.amount');

    expect(Cubes::report()->toArray())->toBe(['from' => null, 'rows' => [], 'columns' => [], 'values' => []]);
});

it('round trips a serialized definition', function () {
    $definition = [
        'from' => 'sales',
        'rows' => ['regions.name'],
        'columns' => ['sales.year'],
        'values' => [
            ['field' => 'sales.amount', 'aggregation' => 'avg', 'label' => 'Average'],
            ['field' => 'sales.id', 'aggregation' => 'count', 'label' => null],
        ],
    ];

    expect(Cubes::report($definition)->toArray())->toBe($definition);
});

it('accepts aggregation names in any case and plain value references', function () {
    $report = Cubes::report([
        'values' => [['field' => 'sales.amount', 'aggregation' => 'SUM'], 'sales.id'],
    ]);

    expect(array_column($report->toArray()['values'], 'aggregation'))->toBe(['sum', 'count']);
});

it('rejects invalid selections', function (Closure $build, string $message) {
    expect($build)->toThrow(CubesException::class, $message);
})->with([
    'metric as row' => [fn () => Cubes::report()->rows('sales.amount'), 'The field [sales.amount] is a metric'],
    'aggregation not allowed' => [fn () => Cubes::report()->value('sales.amount', 'count'), 'The aggregation [count] is not allowed'],
    'unknown aggregation' => [fn () => Cubes::report()->value('sales.amount', 'median'), 'The aggregation [median] is not allowed'],
    'unknown dataset' => [fn () => Cubes::report()->rows('nope.name'), 'The dataset [nope] is not registered.'],
    'unknown field' => [fn () => Cubes::report()->rows('sales.nope'), 'The field [nope] does not exist in the dataset [sales].'],
    'malformed reference' => [fn () => Cubes::report()->rows('sales'), 'The field reference [sales] must use'],
    'unknown base table' => [fn () => Cubes::report()->from('nope'), 'The dataset [nope] is not registered.'],
]);

it('fails when the datasets are not connected', function () {
    Cubes::report()->rows('products.name')->value('sales.amount')->toQuery();
})->throws(CubesException::class, 'There are no relations that connect the dataset [sales] with [products].');

it('fails when the report is empty', function () {
    Cubes::report()->toQuery();
})->throws(CubesException::class, 'The report needs at least one row, column, or value.');

it('translates the exception messages', function () {
    app()->setLocale('es');

    Cubes::report()->rows('sales.amount');
})->throws(CubesException::class, 'El campo [sales.amount] es una métrica');

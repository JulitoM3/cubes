<?php

declare(strict_types=1);

use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Facades\Cubes;

it('aggregates a value grouped by a related dataset, joining through intermediate tables', function () {
    $rows = Cubes::engine()
        ->rows([['key' => 'regions.name']])
        ->values([['key' => 'sales.amount', 'aggregation' => 'sum']])
        ->get();

    expect($rows->all())->toEqual([
        ['regions.name' => null, 'sum(sales.amount)' => 10],
        ['regions.name' => 'North', 'sum(sales.amount)' => 180],
        ['regions.name' => 'South', 'sum(sales.amount)' => 200],
    ]);
});

it('uses the dataset of the first value as the base table', function () {
    $sql = Cubes::engine()
        ->rows([['key' => 'regions.name']])
        ->values([['key' => 'sales.amount']])
        ->build()
        ->toSql();

    expect($sql)->toContain('from "sales"')
        ->and($sql)->toContain('left join "stores" on "sales"."store_id" = "stores"."id"')
        ->and($sql)->toContain('left join "regions" on "stores"."region_id" = "regions"."id"');
});

it('joins each table only once when several fields share the path', function () {
    $sql = Cubes::engine()
        ->rows([['key' => 'regions.name'], ['key' => 'stores.name']])
        ->values([['key' => 'sales.amount']])
        ->build()
        ->toSql();

    expect(substr_count($sql, 'join "stores"'))->toBe(1)
        ->and(substr_count($sql, 'join "regions"'))->toBe(1);
});

it('walks relations in reverse when the base table is set explicitly', function () {
    $rows = Cubes::engine()
        ->from('regions')
        ->rows([['key' => 'regions.name']])
        ->values([['key' => 'sales.id', 'aggregation' => 'count']])
        ->get();

    expect($rows->mapWithKeys(fn (array $row): array => [$row['regions.name'] => $row['count(sales.id)']])->all())
        ->toEqual(['North' => 3, 'South' => 1, 'West' => 0]);
});

it('selects field expressions', function () {
    $rows = Cubes::engine()
        ->rows([['key' => 'sales.short_year']])
        ->values([['key' => 'sales.amount']])
        ->get();

    expect($rows->mapWithKeys(fn (array $row): array => [$row['sales.short_year'] => $row['sum(sales.amount)']])->all())
        ->toEqual([24 => 140, 25 => 250]);
});

it('uses the first allowed aggregation by default and accepts names in any case', function () {
    $definition = Cubes::engine()
        ->values([['key' => 'sales.amount'], ['key' => 'sales.amount', 'aggregation' => 'AVG'], ['key' => 'sales.id']])
        ->reportDefinition();

    expect(array_column($definition->toArray()['values'], 'aggregation'))->toBe(['sum', 'avg', 'count']);
});

it('accepts the palette items as the frontend sends them back, ignoring extra attributes', function () {
    $field = Cubes::dataset('regions')->palette()[0]->toArray();

    $definition = Cubes::engine()->rows([$field])->reportDefinition();

    expect($definition->toArray()['rows'])->toBe([['key' => 'regions.name']]);
});

it('keeps every report independent', function () {
    Cubes::engine()->rows([['key' => 'regions.name']])->values([['key' => 'sales.amount']]);

    expect(Cubes::engine()->reportDefinition()->toArray())
        ->toBe(['from' => null, 'rows' => [], 'columns' => [], 'values' => []]);
});

it('describes the report in the same shape it receives', function () {
    $definition = Cubes::engine()
        ->from('sales')
        ->rows([['key' => 'regions.name']])
        ->columns([['key' => 'sales.year']])
        ->values([['key' => 'sales.amount', 'aggregation' => 'avg', 'label' => 'Average']])
        ->reportDefinition()
        ->toArray();

    expect($definition)->toBe([
        'from' => 'sales',
        'rows' => [['key' => 'regions.name']],
        'columns' => [['key' => 'sales.year']],
        'values' => [['key' => 'sales.amount', 'aggregation' => 'avg', 'label' => 'Average']],
    ]);
});

it('rejects invalid selections', function (Closure $build, string $message) {
    expect($build)->toThrow(CubesException::class, $message);
})->with([
    'metric as row' => [fn () => Cubes::engine()->rows([['key' => 'sales.amount']]), 'The field [sales.amount] is a metric'],
    'aggregation not allowed' => [fn () => Cubes::engine()->values([['key' => 'sales.amount', 'aggregation' => 'count']]), 'The aggregation [count] is not allowed'],
    'unknown aggregation' => [fn () => Cubes::engine()->values([['key' => 'sales.amount', 'aggregation' => 'median']]), 'The aggregation [median] is not allowed'],
    'unknown dataset' => [fn () => Cubes::engine()->rows([['key' => 'nope.name']]), 'The dataset [nope] is not registered.'],
    'unknown field' => [fn () => Cubes::engine()->rows([['key' => 'sales.nope']]), 'The field [nope] does not exist in the dataset [sales].'],
    'injected field' => [fn () => Cubes::engine()->rows([['key' => 'sales.year; drop table sales']]), 'does not exist in the dataset [sales].'],
    'malformed key' => [fn () => Cubes::engine()->rows([['key' => 'sales']]), 'The field reference [sales] must use'],
    'missing key' => [fn () => Cubes::engine()->rows([['field' => 'regions.name']]), 'must be an array with a "key"'],
    'not an array' => [fn () => Cubes::engine()->rows(['regions.name']), 'must be an array with a "key"'],
    'label not a string' => [fn () => Cubes::engine()->values([['key' => 'sales.amount', 'label' => ['x']]]), 'must be an array with a "key"'],
    'unknown base table' => [fn () => Cubes::engine()->from('nope'), 'The dataset [nope] is not registered.'],
]);

it('fails when the datasets are not connected', function () {
    Cubes::engine()
        ->rows([['key' => 'products.name']])
        ->values([['key' => 'sales.amount']])
        ->build();
})->throws(CubesException::class, 'There are no relations that connect the dataset [sales] with [products].');

it('fails when the report is empty', function () {
    Cubes::engine()->build();
})->throws(CubesException::class, 'The report needs at least one row, column, or value.');

it('translates the exception messages', function () {
    app()->setLocale('es');

    Cubes::engine()->rows([['key' => 'sales.amount']]);
})->throws(CubesException::class, 'El campo [sales.amount] es una métrica');

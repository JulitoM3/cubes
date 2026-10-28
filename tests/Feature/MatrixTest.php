<?php

declare(strict_types=1);

use Julitom3\Cubes\Facades\Cubes;

/**
 * @param  array<string, mixed>  $matrix
 * @return list<list<string>>
 */
function headerLabels(array $matrix): array
{
    return array_map(fn (array $row): array => array_column($row, 'label'), $matrix['headers']);
}

/**
 * @param  array<string, mixed>  $matrix
 * @return list<array{list<string>, list<mixed>}>
 */
function bodyValues(array $matrix): array
{
    return array_map(fn (array $row): array => [
        array_column($row['headers'], 'label'),
        array_column($row['cells'], 'value'),
    ], $matrix['rows']);
}

it('builds a matrix with rows only', function () {
    $matrix = Cubes::report()->rows('regions.name')->value('sales.amount')->matrix()->toArray();

    expect(headerLabels($matrix))->toBe([['Region', 'Amount']])
        ->and(bodyValues($matrix))->toEqual([
            [['(empty)'], [10]],
            [['North'], [180]],
            [['South'], [200]],
        ]);
});

it('aligns the cells with the column headers, leaving missing combinations empty', function () {
    $matrix = Cubes::report()
        ->rows('regions.name')
        ->columns('sales.year')
        ->value('sales.amount')
        ->matrix()
        ->toArray();

    expect(headerLabels($matrix))->toBe([['Region', '2024', '2025']])
        ->and(bodyValues($matrix))->toEqual([
            [['(empty)'], [10, null]],
            [['North'], [130, 50]],
            [['South'], [null, 200]],
        ]);
});

it('adds a header row for the values when there are several', function () {
    $matrix = Cubes::report()
        ->rows('regions.name')
        ->columns('sales.year')
        ->value('sales.amount', label: 'Total')
        ->value('sales.id')
        ->matrix()
        ->toArray();

    expect(headerLabels($matrix))->toBe([
        ['Region', '2024', '2025'],
        ['Total', 'Sales count', 'Total', 'Sales count'],
    ])
        ->and($matrix['headers'][0][0])->toMatchArray(['kind' => 'dimension', 'rowspan' => 2])
        ->and($matrix['headers'][0][1])->toMatchArray(['kind' => 'column', 'colspan' => 2, 'value' => 2024])
        ->and($matrix['headers'][1][0])->toMatchArray(['kind' => 'measure', 'colspan' => 1])
        ->and(bodyValues($matrix)[1])->toEqual([['North'], [130, 2, 50, 1]]);
});

it('spans nested column headers over their leaves', function () {
    $matrix = Cubes::report()
        ->columns('regions.name', 'sales.year')
        ->value('sales.id')
        ->matrix()
        ->toArray();

    expect(headerLabels($matrix))->toBe([
        ['(empty)', 'North', 'South'],
        ['2024', '2024', '2025', '2025'],
    ])
        ->and(array_column($matrix['headers'][0], 'colspan'))->toBe([1, 2, 1])
        ->and(bodyValues($matrix))->toEqual([[[], [1, 2, 1, 1]]]);
});

it('builds a single totals row without row dimensions', function () {
    $matrix = Cubes::report()->value('sales.amount')->matrix()->toArray();

    expect(headerLabels($matrix))->toBe([['Amount']])
        ->and(bodyValues($matrix))->toEqual([[[], [390]]])
        ->and($matrix['rows'][0]['cells'][0]['format'])->toBe('currency');
});

it('translates the label of empty values', function () {
    app()->setLocale('es');

    $matrix = Cubes::report()->rows('regions.name')->value('sales.amount')->matrix()->toArray();

    expect($matrix['rows'][0]['headers'][0])->toBe(['label' => '(vacío)', 'value' => null]);
});

it('serializes the matrix to json', function () {
    $matrix = Cubes::report()->rows('regions.name')->value('sales.amount')->matrix();

    expect(json_decode((string) json_encode($matrix), true))->toEqual($matrix->toArray());
});

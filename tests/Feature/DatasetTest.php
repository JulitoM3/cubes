<?php

declare(strict_types=1);

use Julitom3\Cubes\Cubes as CubesManager;
use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\DatasetRegistry;
use Julitom3\Cubes\Datasets\Field;
use Julitom3\Cubes\Datasets\Relation;
use Julitom3\Cubes\Enums\Aggregation;
use Julitom3\Cubes\Enums\FieldFormat;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Facades\Cubes;
use Julitom3\Cubes\Tests\Fixtures\Datasets\SalesDataset;

it('registers the configured datasets', function () {
    expect(array_keys(Cubes::datasets()))->toBe(['regions', 'stores', 'sales', 'products'])
        ->and(Cubes::dataset('sales'))->toBeInstanceOf(SalesDataset::class);
});

it('resolves the manager and the registry as singletons', function () {
    expect(app(CubesManager::class))->toBe(app(CubesManager::class))
        ->and(app(DatasetRegistry::class))->toBe(app(DatasetRegistry::class));
});

it('describes the datasets with stable field keys', function () {
    $catalog = Cubes::catalog();

    expect($catalog)->toBe(Cubes::catalog())
        ->and($catalog[2])->toMatchArray(['name' => 'sales', 'label' => 'Sales'])
        ->and($catalog[2]['fields'][0])->toBe([
            'key' => 'sales.amount',
            'column' => 'amount',
            'label' => 'Amount',
            'type' => 'metric',
            'format' => 'currency',
            'pivotable' => false,
            'aggregations' => ['sum', 'avg', 'min', 'max'],
        ])
        ->and($catalog[0]['fields'][0])->toMatchArray(['key' => 'regions.name', 'pivotable' => true, 'aggregations' => []]);
});

it('registers datasets at runtime', function () {
    $dataset = new class extends Dataset
    {
        public function table(): string
        {
            return 'products';
        }

        public function name(): string
        {
            return 'catalog';
        }

        public function fields(): array
        {
            return [Field::dimension('name')];
        }
    };

    Cubes::register($dataset);

    expect(Cubes::dataset('catalog')->field('name')->label)->toBe('Name')
        ->and(Cubes::report()->rows('catalog.name')->get()->all())->toBe([['catalog.name' => 'Widget']]);
});

it('rejects classes that are not datasets', function () {
    Cubes::register(stdClass::class);
})->throws(CubesException::class, '[stdClass] is not a Cubes dataset.');

it('rejects unsafe identifiers', function (Closure $build) {
    expect($build)->toThrow(CubesException::class, 'may only contain letters, numbers, and underscores');
})->with([
    'field column' => [fn () => Field::dimension('name; drop table sales')],
    'relation table' => [fn () => new Relation('stores s', 'store_id')],
    'relation key' => [fn () => new Relation('stores', 'store_id or 1=1')],
]);

it('allows counting fields without explicit aggregations', function () {
    $field = Field::dimension('name');

    expect($field->allowedAggregations())->toBe([Aggregation::Count])
        ->and($field->allows(Aggregation::Sum))->toBeFalse();
});

it('guesses field formats from column types', function (string $type, FieldFormat $format) {
    expect(FieldFormat::fromColumnType($type))->toBe($format);
})->with([
    ['bigint', FieldFormat::Integer],
    ['TINYINT', FieldFormat::Integer],
    ['decimal', FieldFormat::Decimal],
    ['numeric', FieldFormat::Decimal],
    ['boolean', FieldFormat::Boolean],
    ['date', FieldFormat::Date],
    ['timestamp', FieldFormat::DateTime],
    ['year', FieldFormat::Year],
    ['json', FieldFormat::Text],
]);

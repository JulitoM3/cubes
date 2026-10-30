<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Julitom3\Cubes\Cubes as CubesManager;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Datasets\DTOs\Metric;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Datasets\ModelDataset;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Facades\Cubes;
use Julitom3\Cubes\ReportEngine\DatasetRegistry;
use Julitom3\Cubes\Tests\Fixtures\Models\Catalog\Supplier;
use Julitom3\Cubes\Tests\Fixtures\Models\Sale;
use Julitom3\Cubes\Traits\Cubable;

it('registers the configured models as datasets', function () {
    $sales = Cubes::dataset('sales');

    expect(array_keys(Cubes::datasets()))->toBe(['regions', 'stores', 'sales', 'products'])
        ->and($sales)->toBeInstanceOf(ModelDataset::class)
        ->and($sales instanceof ModelDataset ? $sales->model() : null)->toBeInstanceOf(Sale::class);
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
            'dataset' => 'sales',
            'label' => 'Amount',
            'type' => 'metric',
            'format' => 'currency',
            'pivotable' => false,
            'aggregations' => ['sum', 'avg', 'min', 'max'],
        ])
        ->and($catalog[0]['fields'][0])->toMatchArray(['key' => 'regions.name', 'pivotable' => true, 'aggregations' => []]);
});

it('registers models at runtime', function () {
    Cubes::register(new class extends Model
    {
        use Cubable;

        protected $table = 'products';

        public function cubeSchema(): CubeSchema
        {
            return CubeSchema::make()->category('catalog')->dimensions(new Dimension('name'));
        }
    });

    expect(Cubes::dataset('catalog')->getField('name')->label)->toBe('Name')
        ->and(Cubes::engine()->rows([['key' => 'catalog.name']])->get()->all())->toBe([['catalog.name' => 'Widget']]);
});

it('rejects classes that are not Cubable models', function (string $class) {
    expect(fn () => Cubes::register($class))
        ->toThrow(CubesException::class, "[{$class}] is not an Eloquent model that uses the Cubable trait.");
})->with([
    'not a model' => [stdClass::class],
    'model without the trait' => [Supplier::class],
    'missing class' => ['App\\Models\\Missing'],
]);

it('rejects unsafe identifiers', function (Closure $build) {
    expect($build)->toThrow(CubesException::class, 'may only contain letters, numbers, and underscores');
})->with([
    'dimension column' => [fn () => new Dimension('name; drop table sales')],
    'metric column' => [fn () => new Metric('amount)--')],
    'relation table' => [fn () => new RelationDefinitionDto('stores s', 'store_id')],
    'relation key' => [fn () => new RelationDefinitionDto('stores', 'store_id or 1=1')],
]);

it('only accepts the matching field or relation objects in the schema', function (Closure $build) {
    expect($build)->toThrow(TypeError::class);
})->with([
    'a column name' => [fn () => CubeSchema::make()->dimensions('name')],
    'an array' => [fn () => CubeSchema::make()->dimensions([new Dimension('name')])],
    'a metric as a dimension' => [fn () => CubeSchema::make()->dimensions(new Metric('amount'))],
    'a dimension as a metric' => [fn () => CubeSchema::make()->metrics(new Dimension('name'))],
    'a relation name' => [fn () => CubeSchema::make()->relations('store')],
]);

it('only allows counting dimensions', function () {
    $field = new Dimension('name');

    expect($field->allowedAggregations())->toBe([Aggregation::Count])
        ->and($field->allows(Aggregation::Sum))->toBeFalse();
});

it('allows sum, avg, min, and max on metrics unless told otherwise', function () {
    expect((new Metric('amount'))->allowedAggregations())
        ->toBe([Aggregation::Sum, Aggregation::Avg, Aggregation::Min, Aggregation::Max])
        ->and((new Metric('id', aggregations: [Aggregation::Count]))->defaultAggregation())->toBe(Aggregation::Count)
        ->and((new Metric('amount', aggregations: []))->allowedAggregations())->toBe([Aggregation::Count]);
});

it('keeps every property when the format changes', function () {
    $dimension = (new Dimension('year', 'Year', pivotable: true, expression: 'YEAR(sold_at)'))->withFormat(FieldFormat::Year);
    $metric = (new Metric('amount', 'Amount', [Aggregation::Sum]))->withFormat(FieldFormat::Currency);

    expect($dimension)->toEqual(new Dimension('year', 'Year', FieldFormat::Year, true, 'YEAR(sold_at)'))
        ->and($metric)->toEqual(new Metric('amount', 'Amount', [Aggregation::Sum], FieldFormat::Currency));
});

it('guesses field formats from model casts', function (?string $cast, ?FieldFormat $format) {
    expect(FieldFormat::fromCast($cast))->toBe($format);
})->with([
    ['int', FieldFormat::Integer],
    ['INTEGER', FieldFormat::Integer],
    ['decimal:2', FieldFormat::Decimal],
    ['float', FieldFormat::Decimal],
    ['boolean', FieldFormat::Boolean],
    ['date', FieldFormat::Date],
    ['immutable_datetime', FieldFormat::DateTime],
    ['datetime:Y-m-d', FieldFormat::DateTime],
    ['string', null],
    ['App\\Casts\\Money', null],
    [null, null],
]);

<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Datasets\Enums\FieldType;
use Julitom3\Cubes\Datasets\Enums\JoinType;
use Julitom3\Cubes\Datasets\ModelDataset;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Facades\Cubes;
use Julitom3\Cubes\ReportEngine\DatasetRegistry;
use Julitom3\Cubes\Tests\Fixtures\Models\Catalog\Product;
use Julitom3\Cubes\Tests\Fixtures\Models\Region;
use Julitom3\Cubes\Tests\Fixtures\Models\Sale;
use Julitom3\Cubes\Tests\Fixtures\Models\Store;
use Julitom3\Cubes\Traits\Cubable;

/**
 * A Cubable model over the stores table with the given definition and relation methods.
 */
function storeModel(CubeSchema $definition): Model
{
    $model = new class extends Model
    {
        use Cubable;

        public static CubeSchema $definition;

        protected $table = 'stores';

        protected $casts = ['created_at' => 'datetime', 'region_id' => 'integer'];

        public function cubeSchema(): CubeSchema
        {
            return self::$definition;
        }

        /** @return HasMany<Sale, $this> */
        public function sales(): HasMany
        {
            // An anonymous class has no name to guess the foreign key from.
            return $this->hasMany(Sale::class, 'store_id');
        }

        /** @return BelongsToMany<Product, $this> */
        public function products(): BelongsToMany
        {
            return $this->belongsToMany(Product::class);
        }

        public function notARelation(): string
        {
            return 'nope';
        }
    };

    $model::$definition = $definition;

    return $model;
}

it('builds the dataset from the table, casts, and definition of the model', function () {
    $dataset = new ModelDataset(storeModel(CubeSchema::make()
        ->dimensions(['name', 'created_at'])
        ->metrics(['region_id'])));

    $fields = $dataset->fieldMap();

    expect($dataset->table())->toBe('stores')
        ->and($dataset->category())->toBe('stores')
        ->and($dataset->label())->toBe('Stores')
        ->and(array_keys($fields))->toBe(['name', 'created_at', 'region_id'])
        ->and($fields['name']->type)->toBe(FieldType::Dimension)
        ->and($fields['name']->format)->toBe(FieldFormat::Text)
        ->and($fields['name']->label)->toBe('Name')
        ->and($fields['created_at']->format)->toBe(FieldFormat::DateTime)
        ->and($fields['region_id']->type)->toBe(FieldType::Metric)
        ->and($fields['region_id']->format)->toBe(FieldFormat::Integer)
        ->and($fields['region_id']->label)->toBe('Region Id');
});

it('lets the definition name and label the dataset', function () {
    $dataset = new ModelDataset(storeModel(CubeSchema::make()->category('shops')->label('Our shops')));

    expect($dataset->category())->toBe('shops')
        ->and($dataset->label())->toBe('Our shops');
});

it('reads the join keys from the relation methods of the model', function () {
    $belongsTo = (new ModelDataset(new Store))->relations()->all();
    $hasMany = (new ModelDataset(storeModel(CubeSchema::make()->relations(['sales' => JoinType::Inner]))))->relations()->all();

    expect($belongsTo)->toEqual([new RelationDefinitionDto('regions', 'region_id', 'id', JoinType::Left)])
        ->and($hasMany)->toEqual([new RelationDefinitionDto('sales', 'id', 'store_id', JoinType::Inner)]);
});

it('joins through a has many relation declared on the other side', function () {
    app(DatasetRegistry::class)->register(storeModel(CubeSchema::make()
        ->dimensions(['name'])
        ->relations(['sales'])));

    $sql = Cubes::engine()
        ->from('stores')
        ->rows([['key' => 'stores.name']])
        ->values([['key' => 'sales.amount']])
        ->build()
        ->toSql();

    expect($sql)->toContain('left join "sales" on "stores"."id" = "sales"."store_id"');
});

it('rejects relations it cannot turn into a join', function (string $relation) {
    expect(fn () => (new ModelDataset(storeModel(CubeSchema::make()->relations([$relation]))))->relations())
        ->toThrow(CubesException::class, "The relation [{$relation}] of ");
})->with(['belongs to many' => 'products', 'not a relation' => 'notARelation', 'missing method' => 'missing']);

it('discovers the Cubable models in the configured paths', function () {
    config([
        'cubes.auto_discover' => true,
        'cubes.discover_paths' => [__DIR__.'/../Fixtures/Models', __DIR__.'/missing'],
        'cubes.models' => [],
    ]);

    app()->forgetInstance(DatasetRegistry::class);

    $models = array_map(
        fn ($dataset): string => $dataset instanceof ModelDataset ? $dataset->model()::class : '',
        Cubes::datasets(),
    );

    expect(array_values($models))->toBe([Product::class, Region::class, Sale::class, Store::class]);
});

it('registers only the configured models when discovery is off', function () {
    config(['cubes.auto_discover' => false, 'cubes.models' => [Region::class]]);

    app()->forgetInstance(DatasetRegistry::class);

    expect(array_keys(Cubes::datasets()))->toBe(['regions']);
});

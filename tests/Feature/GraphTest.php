<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\JoinType;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Facades\Cubes;
use Julitom3\Cubes\ReportEngine\Definitions\DatasetGraphDefinition;
use Julitom3\Cubes\ReportEngine\Definitions\DatasetPathDefinition;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetEdgeDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph\DatasetNodeDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\JoinDefinitionDto;
use Julitom3\Cubes\ReportEngine\ReportEngine;
use Julitom3\Cubes\Tests\Fixtures\Datasets\SalesDataset;

/**
 * @return list<JoinDefinitionDto>
 */
function joinsOf(ReportEngine $engine): array
{
    return $engine->queryDefinition()->joins;
}

it('walks every relation in both directions, swapping the keys of the reverse edge', function () {
    $graph = app(DatasetGraphDefinition::class)->create();

    expect($graph->getNodeByTable('sales')->getEdge('stores'))
        ->toEqual(new DatasetEdgeDto('sales', 'stores', 'store_id', 'id', JoinType::Left))
        ->and($graph->getNodeByTable('stores')->getEdge('sales'))
        ->toEqual(new DatasetEdgeDto('stores', 'sales', 'id', 'store_id', JoinType::Left))
        ->and($graph->getNodeByTable('products')->edges)->toBe([])
        ->and($graph->getNodeByTable('stores')->degree())->toBe(2);
});

it('keeps the graph until another dataset is registered', function () {
    $graphs = app(DatasetGraphDefinition::class);
    $graph = $graphs->create();

    expect($graphs->create())->toBe($graph);

    Cubes::register(new SalesDataset);

    expect($graphs->create())->not->toBe($graph)
        ->and($graphs->create())->toBe($graphs->create());
});

it('finds the shortest path with its nodes in order', function () {
    $graph = app(DatasetGraphDefinition::class)->create();

    $path = app(DatasetPathDefinition::class)->create($graph, $graph->getNodeByTable('sales'), $graph->getNodeByTable('regions'));

    expect(array_map(fn (DatasetNodeDto $node): string => $node->table, $path->nodes))->toBe(['sales', 'stores', 'regions'])
        ->and($path->origin()->table)->toBe('sales')
        ->and($path->destination()->table)->toBe('regions')
        ->and($path->hops())->toBe(2)
        ->and($path->isEmpty())->toBeFalse()
        ->and(array_map(fn (DatasetNodeDto $node): string => $node->table, $path->intermediateNodes()))->toBe(['stores'])
        ->and($path->contains('stores'))->toBeTrue()
        ->and($path->contains('products'))->toBeFalse()
        ->and(array_map(fn (array $pair): array => [$pair[0]->table, $pair[1]->table], $path->pairs()))
        ->toBe([['sales', 'stores'], ['stores', 'regions']]);
});

it('returns a one node path when the origin is the destination', function () {
    $graph = app(DatasetGraphDefinition::class)->create();
    $sales = $graph->getNodeByTable('sales');

    $path = app(DatasetPathDefinition::class)->create($graph, $sales, $sales);

    expect($path->nodes)->toBe([$sales])
        ->and($path->isEmpty())->toBeTrue()
        ->and($path->pairs())->toBe([]);
});

it('fails when no path connects two datasets', function () {
    $graph = app(DatasetGraphDefinition::class)->create();

    app(DatasetPathDefinition::class)->create($graph, $graph->getNodeByTable('sales'), $graph->getNodeByTable('products'));
})->throws(CubesException::class, 'There are no relations that connect the dataset [sales] with [products].');

it('adds no joins when every field belongs to the root', function () {
    expect(joinsOf(Cubes::engine()->rows([['key' => 'sales.year']])->values([['key' => 'sales.amount']])))->toBe([]);
});

it('adds one join for a direct relation', function () {
    expect(joinsOf(Cubes::engine()->rows([['key' => 'stores.name']])->values([['key' => 'sales.amount']])))
        ->toEqual([new JoinDefinitionDto('stores', 'sales', 'store_id', 'id', JoinType::Left)]);
});

it('joins a shared intermediate dataset once, in path order', function () {
    $joins = joinsOf(Cubes::engine()
        ->rows([['key' => 'regions.name'], ['key' => 'stores.name']])
        ->values([['key' => 'sales.amount']]));

    expect(array_map(fn (JoinDefinitionDto $join): string => $join->table, $joins))->toBe(['stores', 'regions']);
});

it('orients the keys of relations walked in reverse', function () {
    $engine = Cubes::engine()
        ->from('regions')
        ->rows([['key' => 'regions.name']])
        ->values([['key' => 'sales.id', 'aggregation' => 'count']]);

    expect(joinsOf($engine))->toEqual([
        new JoinDefinitionDto('stores', 'regions', 'id', 'region_id', JoinType::Left),
        new JoinDefinitionDto('sales', 'stores', 'id', 'store_id', JoinType::Left),
    ])
        ->and($engine->build()->toSql())
        ->toContain('left join "stores" on "regions"."id" = "stores"."region_id"')
        ->toContain('left join "sales" on "stores"."id" = "sales"."store_id"');
});

it('never joins the base table again', function (ReportEngine $engine) {
    $from = $engine->queryDefinition()->from->table;

    expect(array_column(joinsOf($engine), 'table'))->not->toContain($from);
})->with([
    'from the fact' => fn () => Cubes::engine()->rows([['key' => 'regions.name'], ['key' => 'stores.name']])->values([['key' => 'sales.amount']]),
    'from a catalog' => fn () => Cubes::engine()->from('regions')->rows([['key' => 'stores.name']])->values([['key' => 'sales.amount']]),
    'without values' => fn () => Cubes::engine()->rows([['key' => 'regions.name'], ['key' => 'sales.year']]),
]);

it('compares columns that exist on their own tables', function (ReportEngine $engine) {
    foreach (joinsOf($engine) as $join) {
        expect(Schema::hasColumn($join->from, $join->localKey))->toBeTrue("{$join->from}.{$join->localKey} does not exist")
            ->and(Schema::hasColumn($join->table, $join->foreignKey))->toBeTrue("{$join->table}.{$join->foreignKey} does not exist");
    }
})->with([
    'forward' => fn () => Cubes::engine()->rows([['key' => 'regions.name']])->values([['key' => 'sales.amount']]),
    'reverse' => fn () => Cubes::engine()->from('regions')->rows([['key' => 'regions.name']])->values([['key' => 'sales.amount']]),
]);

it('keeps the join type of the relation in both directions', function () {
    Cubes::register(new class extends DatasetContract
    {
        public function table(): string
        {
            return 'products';
        }

        public function fields(): FieldCollection
        {
            return new FieldCollection([FieldDefinitionDto::dimension('name')]);
        }

        public function relations(): RelationCollection
        {
            return new RelationCollection([new RelationDefinitionDto('regions', 'id', 'id', JoinType::Inner)]);
        }
    });

    $forward = joinsOf(Cubes::engine()->from('products')->rows([['key' => 'products.name'], ['key' => 'regions.name']]));
    $reverse = joinsOf(Cubes::engine()->from('regions')->rows([['key' => 'regions.name'], ['key' => 'products.name']]));

    expect($forward)->toEqual([new JoinDefinitionDto('regions', 'products', 'id', 'id', JoinType::Inner)])
        ->and($reverse)->toEqual([new JoinDefinitionDto('products', 'regions', 'id', 'id', JoinType::Inner)]);
});

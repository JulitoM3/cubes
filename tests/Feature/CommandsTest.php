<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;

beforeEach(function () {
    $this->path = sys_get_temp_dir().'/cubes-tests-'.uniqid();

    config([
        'cubes.generator.path' => $this->path,
        'cubes.generator.namespace' => 'Generated\\Cubes',
    ]);
});

afterEach(function () {
    File::deleteDirectory($this->path);
});

it('creates a dataset with fields and relations read from the schema', function () {
    $this->artisan('cubes:make-dataset', ['table' => 'stores'])
        ->expectsOutputToContain('StoresDataset.php')
        ->assertSuccessful();

    $contents = File::get("{$this->path}/StoresDataset.php");

    expect($contents)
        ->toContain('namespace Generated\Cubes;')
        ->toContain("FieldDefinitionDto::dimension('name', 'Name', FieldFormat::Text),")
        ->toContain("FieldDefinitionDto::dimension('region_id', 'Region Id', FieldFormat::Integer),")
        ->toContain("new RelationDefinitionDto('regions', 'region_id', 'id'),")
        ->not->toContain("'created_at'")
        ->not->toContain("FieldDefinitionDto::dimension('id'");

    require_once "{$this->path}/StoresDataset.php";

    $dataset = new ('Generated\\Cubes\\StoresDataset');

    expect($dataset)->toBeInstanceOf(DatasetContract::class)
        ->and($dataset->table())->toBe('stores')
        ->and($dataset->getField('region_id')->format)->toBe(FieldFormat::Integer)
        ->and($dataset->relations()[0]->table)->toBe('regions');
});

it('creates an empty dataset', function () {
    $this->artisan('cubes:make-dataset', ['table' => 'sales', '--empty' => true])->assertSuccessful();

    expect(File::get("{$this->path}/SalesDataset.php"))
        ->toContain("return 'sales';")
        ->toContain('return new FieldCollection([]);')
        ->not->toContain('FieldDefinitionDto::dimension');
});

it('does not overwrite an existing dataset unless forced', function () {
    File::ensureDirectoryExists($this->path);
    File::put("{$this->path}/SalesDataset.php", 'custom');

    $this->artisan('cubes:make-dataset', ['table' => 'sales'])
        ->expectsOutputToContain('already exists')
        ->assertFailed();

    expect(File::get("{$this->path}/SalesDataset.php"))->toBe('custom');

    $this->artisan('cubes:make-dataset', ['table' => 'sales', '--force' => true])->assertSuccessful();

    expect(File::get("{$this->path}/SalesDataset.php"))->toContain('class SalesDataset extends DatasetContract');
});

it('fails for unknown tables', function () {
    $this->artisan('cubes:make-dataset', ['table' => 'missing'])
        ->expectsOutputToContain('The table [missing] does not exist.')
        ->assertFailed();
});

it('uses a published stub', function () {
    File::ensureDirectoryExists(base_path('stubs/cubes'));
    File::put(base_path('stubs/cubes/dataset.stub'), 'custom {{ class }}');

    try {
        $this->artisan('cubes:make-dataset', ['table' => 'sales'])->assertSuccessful();

        expect(File::get("{$this->path}/SalesDataset.php"))->toBe('custom SalesDataset');
    } finally {
        File::deleteDirectory(base_path('stubs/cubes'));
    }
});

it('syncs the configured tables, skipping existing datasets', function () {
    config(['cubes.generator.tables' => ['regions', 'stores', 'missing']]);

    $this->artisan('cubes:sync')
        ->expectsOutputToContain('The table [missing] does not exist.')
        ->expectsOutputToContain('2 dataset(s) written.')
        ->assertSuccessful();

    expect(File::exists("{$this->path}/RegionsDataset.php"))->toBeTrue()
        ->and(File::exists("{$this->path}/StoresDataset.php"))->toBeTrue();

    $this->artisan('cubes:sync')
        ->expectsOutputToContain('0 dataset(s) written.')
        ->assertSuccessful();

    $this->artisan('cubes:sync', ['--force' => true])
        ->expectsOutputToContain('2 dataset(s) written.')
        ->assertSuccessful();
});

it('warns when there are no tables to sync', function () {
    $this->artisan('cubes:sync')
        ->expectsOutputToContain('There are no tables to sync.')
        ->assertSuccessful();
});

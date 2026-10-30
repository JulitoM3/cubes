<?php

declare(strict_types=1);

use Julitom3\Cubes\Tests\Fixtures\Models\Region;
use Julitom3\Cubes\Tests\Fixtures\Models\Sale;

it('lists the registered Cubable models with their fields and relations', function () {
    $this->artisan('cubes:list')
        ->expectsOutputToContain('Model auto-discovery is disabled')
        ->expectsTable(
            ['Model', 'Dataset', 'Table', 'Dimensions', 'Metrics', 'Relations'],
            [
                [Region::class, 'regions', 'regions', 1, 0, ''],
                ['Julitom3\Cubes\Tests\Fixtures\Models\Store', 'stores', 'stores', 1, 0, 'regions'],
                [Sale::class, 'sales', 'sales', 2, 2, 'stores'],
                ['Julitom3\Cubes\Tests\Fixtures\Models\Catalog\Product', 'products', 'products', 1, 0, ''],
            ],
        )
        ->assertSuccessful();
});

it('shows the discover paths when auto-discovery is enabled', function () {
    config([
        'cubes.auto_discover' => true,
        'cubes.discover_paths' => [__DIR__.'/../Fixtures/Models'],
    ]);

    $this->artisan('cubes:list')
        ->expectsOutputToContain('Model auto-discovery is enabled in:')
        ->expectsOutputToContain(Sale::class)
        ->assertSuccessful();
});

it('explains how to add models when none are registered', function () {
    config(['cubes.models' => []]);

    $this->artisan('cubes:list')
        ->expectsOutputToContain('No Cubable models were found.')
        ->expectsOutputToContain('Add the Cubable trait to your models')
        ->assertSuccessful();
});

it('translates its output', function () {
    app()->setLocale('es');

    $this->artisan('cubes:list')
        ->expectsOutputToContain('El autodescubrimiento de modelos está deshabilitado')
        ->expectsOutputToContain('Dimensiones')
        ->assertSuccessful();
});

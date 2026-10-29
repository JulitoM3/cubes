<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Julitom3\Cubes\CubesServiceProvider;
use Julitom3\Cubes\Tests\Fixtures\Models\Catalog\Product;
use Julitom3\Cubes\Tests\Fixtures\Models\Region;
use Julitom3\Cubes\Tests\Fixtures\Models\Sale;
use Julitom3\Cubes\Tests\Fixtures\Models\Store;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CubesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cubes.auto_discover', false);
        $app['config']->set('cubes.models', [
            Region::class,
            Store::class,
            Sale::class,
            Product::class,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('region_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->integer('year');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        DB::table('regions')->insert([
            ['id' => 1, 'name' => 'North'],
            ['id' => 2, 'name' => 'South'],
            ['id' => 3, 'name' => 'West'],
        ]);

        DB::table('stores')->insert([
            ['id' => 1, 'name' => 'A', 'region_id' => 1],
            ['id' => 2, 'name' => 'B', 'region_id' => 1],
            ['id' => 3, 'name' => 'C', 'region_id' => 2],
            ['id' => 4, 'name' => 'D', 'region_id' => null],
        ]);

        DB::table('sales')->insert([
            ['store_id' => 1, 'amount' => 100, 'year' => 2024],
            ['store_id' => 1, 'amount' => 50, 'year' => 2025],
            ['store_id' => 2, 'amount' => 30, 'year' => 2024],
            ['store_id' => 3, 'amount' => 200, 'year' => 2025],
            ['store_id' => 4, 'amount' => 10, 'year' => 2024],
        ]);

        DB::table('products')->insert([['name' => 'Widget']]);
    }
}

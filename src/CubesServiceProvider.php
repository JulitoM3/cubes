<?php

declare(strict_types=1);

namespace Julitom3\Cubes;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Julitom3\Cubes\Console\Commands\MakeDatasetCommand;
use Julitom3\Cubes\Console\Commands\SyncDatasetsCommand;
use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\DatasetRegistry;

class CubesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cubes.php', 'cubes');

        $this->app->singleton(DatasetRegistry::class, function (Application $app): DatasetRegistry {
            $registry = new DatasetRegistry($app);

            /** @var list<class-string<Dataset>> $datasets */
            $datasets = $app->make('config')->get('cubes.datasets', []);

            foreach ($datasets as $dataset) {
                $registry->register($dataset);
            }

            return $registry;
        });

        $this->app->singleton(Cubes::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'cubes');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/cubes.php' => config_path('cubes.php'),
        ], ['cubes', 'cubes-config']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/cubes'),
        ], ['cubes', 'cubes-lang']);

        $this->publishes([
            __DIR__.'/../stubs/dataset.stub' => base_path('stubs/cubes/dataset.stub'),
        ], 'cubes-stubs');

        $this->commands([
            MakeDatasetCommand::class,
            SyncDatasetsCommand::class,
        ]);
    }
}

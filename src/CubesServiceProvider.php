<?php

declare(strict_types=1);

namespace Julitom3\Cubes;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Julitom3\Cubes\Console\Commands\InstallCommand;
use Julitom3\Cubes\Datasets\ModelDiscovery;
use Julitom3\Cubes\ReportEngine\DatasetRegistry;
use Julitom3\Cubes\ReportEngine\Definitions\DatasetGraphDefinition;

class CubesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cubes.php', 'cubes');

        // The registry is resolved on first use, so models are only discovered when a report needs them.
        $this->app->singleton(DatasetRegistry::class, function (Application $app): DatasetRegistry {
            $registry = new DatasetRegistry($app);
            $config = $app->make('config');

            /** @var list<class-string<Model>> $models */
            $models = $config->get('cubes.auto_discover')
                ? $app->make(ModelDiscovery::class)->discover($config->get('cubes.discover_paths', []))
                : $config->get('cubes.models', []);

            foreach ($models as $model) {
                $registry->register($model);
            }

            return $registry;
        });

        $this->app->singleton(DatasetGraphDefinition::class);
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

        $this->commands([
            InstallCommand::class,
        ]);
    }
}

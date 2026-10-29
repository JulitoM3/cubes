<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Julitom3\Cubes\ReportEngine\ReportEngine engine()
 * @method static \Julitom3\Cubes\Datasets\Contracts\DatasetContract register(class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model $model)
 * @method static \Julitom3\Cubes\Datasets\Contracts\DatasetContract dataset(string $name)
 * @method static array<string, \Julitom3\Cubes\Datasets\Contracts\DatasetContract> datasets()
 * @method static list<array<string, mixed>> catalog()
 *
 * @see \Julitom3\Cubes\Cubes
 */
class Cubes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Julitom3\Cubes\Cubes::class;
    }
}

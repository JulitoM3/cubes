<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Julitom3\Cubes\Reports\Report report(array<string, mixed> $definition = [])
 * @method static \Julitom3\Cubes\Datasets\Dataset register(class-string<\Julitom3\Cubes\Datasets\Dataset>|\Julitom3\Cubes\Datasets\Dataset $dataset)
 * @method static \Julitom3\Cubes\Datasets\Dataset dataset(string $name)
 * @method static array<string, \Julitom3\Cubes\Datasets\Dataset> datasets()
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

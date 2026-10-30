<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Traits\Cubable;

class Region extends Model
{
    use Cubable;

    public $timestamps = false;

    public function cubeSchema(): CubeSchema
    {
        return CubeSchema::make()
            ->dimensions(new Dimension('name', 'Region', pivotable: true));
    }
}

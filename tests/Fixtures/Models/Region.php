<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Traits\Cubable;

class Region extends Model
{
    use Cubable;

    public $timestamps = false;

    public function cubeSchema(): CubeSchema
    {
        return CubeSchema::make()
            ->dimensions([FieldDefinitionDto::dimension('name', 'Region', pivotable: true)]);
    }
}

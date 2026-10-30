<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Datasets\DTOs\Relation;
use Julitom3\Cubes\Traits\Cubable;

class Store extends Model
{
    use Cubable;

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function cubeSchema(): CubeSchema
    {
        return CubeSchema::make()
            ->dimensions(new Dimension('name', 'Store'))
            ->relations(new Relation('region'));
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Datasets\DTOs\Metric;
use Julitom3\Cubes\Datasets\DTOs\Relation;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Traits\Cubable;

class Sale extends Model
{
    use Cubable;

    public $timestamps = false;

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cubeSchema(): CubeSchema
    {
        return CubeSchema::make()
            ->metrics(
                new Metric('amount', 'Amount', format: FieldFormat::Currency),
                new Metric('id', 'Sales count', [Aggregation::Count]),
            )
            ->dimensions(
                new Dimension('year', 'Year', FieldFormat::Year, pivotable: true),
                new Dimension('short_year', 'Short year', FieldFormat::Integer, expression: 'sales.year - 2000'),
            )
            ->relations(new Relation('store'));
    }
}

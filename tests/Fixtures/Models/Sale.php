<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
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
            ->metrics([
                FieldDefinitionDto::metric('amount', 'Amount', format: FieldFormat::Currency),
                FieldDefinitionDto::metric('id', 'Sales count', [Aggregation::Count]),
            ])
            ->dimensions([
                FieldDefinitionDto::dimension('year', 'Year', FieldFormat::Year, pivotable: true),
                FieldDefinitionDto::dimension('short_year', 'Short year', FieldFormat::Integer, expression: 'sales.year - 2000'),
            ])
            ->relations(['store']);
    }
}

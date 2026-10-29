<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Datasets;

use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;

class SalesDataset extends DatasetContract
{
    public function table(): string
    {
        return 'sales';
    }

    public function fields(): FieldCollection
    {
        return new FieldCollection([
            FieldDefinitionDto::metric('amount', 'Amount', format: FieldFormat::Currency),
            FieldDefinitionDto::metric('id', 'Sales count', [Aggregation::Count]),
            FieldDefinitionDto::dimension('year', 'Year', FieldFormat::Year, pivotable: true),
            FieldDefinitionDto::dimension('short_year', 'Short year', FieldFormat::Integer, expression: 'sales.year - 2000'),
        ]);
    }

    public function relations(): RelationCollection
    {
        return new RelationCollection([
            new RelationDefinitionDto('stores', 'store_id'),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Datasets;

use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;
use Julitom3\Cubes\Datasets\Relation;
use Julitom3\Cubes\Enums\Aggregation;
use Julitom3\Cubes\Enums\FieldFormat;

class SalesDataset extends Dataset
{
    public function table(): string
    {
        return 'sales';
    }

    public function fields(): array
    {
        return [
            Field::metric('amount', 'Amount', format: FieldFormat::Currency),
            Field::metric('id', 'Sales count', [Aggregation::Count]),
            Field::dimension('year', 'Year', FieldFormat::Year, pivotable: true),
            Field::dimension('short_year', 'Short year', FieldFormat::Integer, expression: 'sales.year - 2000'),
        ];
    }

    public function relations(): array
    {
        return [
            new Relation('stores', 'store_id'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Datasets;

use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;

class RegionsDataset extends Dataset
{
    public function table(): string
    {
        return 'regions';
    }

    public function fields(): array
    {
        return [
            Field::dimension('name', 'Region', pivotable: true),
        ];
    }
}

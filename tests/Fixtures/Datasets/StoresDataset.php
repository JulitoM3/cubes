<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Datasets;

use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;
use Julitom3\Cubes\Datasets\Relation;

class StoresDataset extends Dataset
{
    public function table(): string
    {
        return 'stores';
    }

    public function fields(): array
    {
        return [
            Field::dimension('name', 'Store'),
        ];
    }

    public function relations(): array
    {
        return [
            new Relation('regions', 'region_id'),
        ];
    }
}

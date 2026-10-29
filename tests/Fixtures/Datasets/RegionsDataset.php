<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Datasets;

use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;

class RegionsDataset extends DatasetContract
{
    public function table(): string
    {
        return 'regions';
    }

    public function fields(): FieldCollection
    {
        return new FieldCollection([
            FieldDefinitionDto::dimension('name', 'Region', pivotable: true),
        ]);
    }
}

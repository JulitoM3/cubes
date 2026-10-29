<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Datasets;

use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;

class StoresDataset extends DatasetContract
{
    public function table(): string
    {
        return 'stores';
    }

    public function fields(): FieldCollection
    {
        return new FieldCollection([
            FieldDefinitionDto::dimension('name', 'Store'),
        ]);
    }

    public function relations(): RelationCollection
    {
        return new RelationCollection([
            new RelationDefinitionDto('regions', 'region_id'),
        ]);
    }
}

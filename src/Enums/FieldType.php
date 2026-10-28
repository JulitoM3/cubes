<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Enums;

enum FieldType: string
{
    case Dimension = 'dimension';
    case Metric = 'metric';
}

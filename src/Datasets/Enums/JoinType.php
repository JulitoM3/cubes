<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\Enums;

enum JoinType: string
{
    case Inner = 'inner';
    case Left = 'left';
    case Right = 'right';
}

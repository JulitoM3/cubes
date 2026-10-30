<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\DTOs;

use Julitom3\Cubes\Datasets\Enums\JoinType;

/**
 * A relation method of the model, e.g. "store", that connects it with another Cubable model.
 * The join keys are read from the relation itself.
 */
final readonly class Relation
{
    public function __construct(
        public string $name,
        public JoinType $joinType = JoinType::Left,
    ) {}
}

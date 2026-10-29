<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Traits;

use Illuminate\Database\Eloquent\Model;
use Julitom3\Cubes\Datasets\CubeSchema;

/**
 * Make an Eloquent model reportable: it declares which of its columns and relations Cubes can use.
 *
 * @phpstan-require-extends Model
 */
trait Cubable
{
    /**
     * The dimensions, metrics, and relations reports can use, e.g.
     * CubeSchema::make()->dimensions(['year'])->metrics(['amount'])->relations(['store']).
     */
    abstract public function cubeSchema(): CubeSchema;
}

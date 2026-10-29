<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Tests\Fixtures\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

/**
 * A model without the Cubable trait, which discovery must skip.
 */
class Supplier extends Model {}

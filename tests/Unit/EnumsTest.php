<?php

declare(strict_types=1);

use Julitom3\Cubes\Enums\Aggregation;

it('resolves aggregations by name in any case', function () {
    expect(Aggregation::fromName(' SUM '))->toBe(Aggregation::Sum)
        ->and(Aggregation::fromName('avg'))->toBe(Aggregation::Avg)
        ->and(Aggregation::fromName('median'))->toBeNull();
});

it('compiles aggregations to their sql function', function () {
    expect(array_map(fn (Aggregation $aggregation): string => $aggregation->sql(), Aggregation::cases()))
        ->toBe(['COUNT', 'SUM', 'AVG', 'MIN', 'MAX']);
});

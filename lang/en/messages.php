<?php

declare(strict_types=1);

return [
    'empty' => '(empty)',

    'exceptions' => [
        'not_cubable' => '[:class] is not an Eloquent model that uses the Cubable trait.',
        'unsupported_relation' => 'The relation [:relation] of [:model] must be a BelongsTo, HasOne, or HasMany relation method.',
        'dataset_not_found' => 'The dataset [:dataset] is not registered.',
        'field_not_found' => 'The field [:field] does not exist in the dataset [:dataset].',
        'invalid_field_reference' => 'The field reference [:reference] must use the "dataset.field" format.',
        'invalid_identifier' => 'The identifier [:identifier] may only contain letters, numbers, and underscores.',
        'invalid_selection' => 'Every selected field must be an array with a "key", and its "aggregation" and "label" must be strings.',
        'not_a_dimension' => 'The field [:field] is a metric and cannot be used as a row or column.',
        'invalid_aggregation' => 'The aggregation [:aggregation] is not allowed for the field [:field].',
        'no_path' => 'There are no relations that connect the dataset [:from] with [:to].',
        'empty_report' => 'The report needs at least one row, column, or value.',
    ],

    'commands' => [
        'published' => 'published',
        'installed' => 'Cubes installed successfully.',
        'next_steps' => 'Add the Cubable trait to your models and define their dimensions, metrics, and relations in cubeSchema().',
    ],
];

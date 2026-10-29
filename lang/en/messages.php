<?php

declare(strict_types=1);

return [
    'empty' => '(empty)',

    'exceptions' => [
        'invalid_dataset' => '[:class] is not a Cubes dataset.',
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
        'table_not_found' => 'The table [:table] does not exist.',
        'dataset_exists' => 'The dataset [:path] already exists. Use --force to overwrite it.',
        'dataset_created' => 'Dataset [:path] created successfully.',
        'no_tables' => 'There are no tables to sync. Add them to cubes.generator.tables.',
        'skipped' => 'skipped, already exists',
        'written' => 'written',
        'synced' => ':count dataset(s) written.',
        'published' => 'published',
        'installed' => 'Cubes installed successfully.',
        'next_steps' => 'Create your datasets with php artisan cubes:make-dataset {table} and register them in config/cubes.php.',
    ],
];

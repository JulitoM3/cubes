<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Datasets
    |--------------------------------------------------------------------------
    |
    | The dataset classes that reports can use. Each one describes a table,
    | its fields, and its relations to the other datasets.
    |
    */

    'datasets' => [
        // App\Cubes\Datasets\SalesDataset::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The connection that reports and the dataset generator use. Leave it
    | null to use the default connection.
    |
    */

    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | Dataset Generator
    |--------------------------------------------------------------------------
    |
    | Used by the cubes:make-dataset and cubes:sync commands. When path is
    | null, datasets are written to app/Cubes/Datasets.
    |
    */

    'generator' => [

        'path' => null,

        'namespace' => 'App\\Cubes\\Datasets',

        // The tables that cubes:sync creates datasets for.
        'tables' => [],

        'exclude_columns' => ['id', 'created_at', 'updated_at', 'deleted_at'],

    ],

];

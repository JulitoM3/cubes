<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Model Discovery
    |--------------------------------------------------------------------------
    |
    | Every Eloquent model that uses the Cubable trait is a dataset. When
    | auto_discover is true, Cubes finds them in the discover_paths. When it
    | is false, only the models listed below are registered.
    |
    */

    'auto_discover' => true,

    'discover_paths' => [
        app_path('Models'),
    ],

    'models' => [
        // App\Models\Sale::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The connection that reports run on. Leave it null to use the default
    | connection.
    |
    */

    'connection' => null,

];

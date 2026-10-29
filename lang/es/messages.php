<?php

declare(strict_types=1);

return [
    'empty' => '(vacío)',

    'exceptions' => [
        'not_cubable' => '[:class] no es un modelo Eloquent que use el trait Cubable.',
        'unsupported_relation' => 'La relación [:relation] de [:model] debe ser un método de relación BelongsTo, HasOne o HasMany.',
        'dataset_not_found' => 'El dataset [:dataset] no está registrado.',
        'field_not_found' => 'El campo [:field] no existe en el dataset [:dataset].',
        'invalid_field_reference' => 'La referencia [:reference] debe tener el formato "dataset.campo".',
        'invalid_identifier' => 'El identificador [:identifier] solo puede contener letras, números y guiones bajos.',
        'invalid_selection' => 'Cada campo seleccionado debe ser un arreglo con una "key", y su "aggregation" y "label" deben ser textos.',
        'not_a_dimension' => 'El campo [:field] es una métrica y no se puede usar como fila o columna.',
        'invalid_aggregation' => 'La agregación [:aggregation] no está permitida para el campo [:field].',
        'no_path' => 'No hay relaciones que conecten el dataset [:from] con [:to].',
        'empty_report' => 'El reporte necesita al menos una fila, columna o valor.',
    ],

    'commands' => [
        'published' => 'publicado',
        'installed' => 'Cubes se instaló correctamente.',
        'next_steps' => 'Agrega el trait Cubable a tus modelos y define sus dimensiones, métricas y relaciones en cubeSchema().',
    ],
];

<?php

declare(strict_types=1);

return [
    'empty' => '(vacío)',

    'exceptions' => [
        'invalid_dataset' => '[:class] no es un dataset de Cubes.',
        'dataset_not_found' => 'El dataset [:dataset] no está registrado.',
        'field_not_found' => 'El campo [:field] no existe en el dataset [:dataset].',
        'invalid_field_reference' => 'La referencia [:reference] debe tener el formato "dataset.campo".',
        'invalid_identifier' => 'El identificador [:identifier] solo puede contener letras, números y guiones bajos.',
        'not_a_dimension' => 'El campo [:field] es una métrica y no se puede usar como fila o columna.',
        'invalid_aggregation' => 'La agregación [:aggregation] no está permitida para el campo [:field].',
        'no_path' => 'No hay relaciones que conecten el dataset [:from] con [:to].',
        'empty_report' => 'El reporte necesita al menos una fila, columna o valor.',
    ],

    'commands' => [
        'table_not_found' => 'La tabla [:table] no existe.',
        'dataset_exists' => 'El dataset [:path] ya existe. Usa --force para sobrescribirlo.',
        'dataset_created' => 'Dataset [:path] creado correctamente.',
        'no_tables' => 'No hay tablas para sincronizar. Agrégalas en cubes.generator.tables.',
        'skipped' => 'omitido, ya existe',
        'written' => 'escrito',
        'synced' => ':count dataset(s) escritos.',
    ],
];

---
name: cubes-development
description: >
  Build pivot-table reports with the julitom3/cubes package: define datasets, generate them from the schema, and query rows, columns, and values.
license: MIT
metadata:
  author: JulitoM3
---

# Cubes

Use this skill when a Laravel application needs reports, pivot tables, or a report builder powered by `julitom3/cubes`.

## Workflow

### 1. Install and define datasets

- After `composer require julitom3/cubes`, run `php artisan cubes:install` to publish the config and translations; add `--with-stubs` to also publish the dataset stub, and `--force` to overwrite published files.

- Generate a dataset per table with `php artisan cubes:make-dataset {table}`, or list tables in `cubes.generator.tables` and run `php artisan cubes:sync`.
- Datasets extend `Julitom3\Cubes\Datasets\Contracts\DatasetContract` and implement `table()`, `fields(): FieldCollection`, and optionally `relations(): RelationCollection` and `category()`.
- Use `FieldDefinitionDto::dimension()` for fields that rows and columns group by, and `FieldDefinitionDto::metric()` for fields aggregated as values.
- Declare `new RelationDefinitionDto('related_table', 'local_key', 'foreign_key')` so Cubes can resolve joins; declare each relation once, it is walked in both directions.
- Register every dataset in `config/cubes.php` under `datasets`.

### 2. Build reports

```php
use Julitom3\Cubes\Facades\Cubes;

Cubes::engine()
    ->rows([['key' => 'regions.name']])
    ->columns([['key' => 'sales.year']])
    ->values([['key' => 'sales.amount', 'aggregation' => 'sum', 'label' => 'Total sales']])
    ->matrixRender();
```

- Every selected field is an array with a `key` such as `dataset.field`; values may add `aggregation` and `label`.
- `get()` returns flat rows, `matrixRender()` returns a `MatrixRenderDto` that serializes to `headers` and `body.rows`, and `build()` returns the query builder.
- `reportDefinition()` and `queryDefinition()` return the DTOs of each step; the `QueryDefinitionDto` has one DTO per SQL clause (from, selects, joins, group by, order by).
- `ReportEngine` only orchestrates: the graph (`DatasetGraphDefinition`), root (`DatasetTraversalResolver`), paths (`DatasetPathDefinition`), clause factories, `SqlCompiler`, and the matrix tree factory and walkers each own their step.
- For drag-and-drop frontends, send `Cubes::catalog()` as the palette and pass the dropped items back to `rows()`, `columns()`, and `values()`.

## Anti-patterns

- Do not build report SQL by hand; let Cubes resolve joins from the dataset relations.
- Do not add SQL to the query builder outside the clause compilers; extend the query definition DTOs instead.
- Do not put user input in a field `expression`; expressions are trusted SQL defined in the dataset.
- Do not reuse an engine for different reports; call `Cubes::engine()` for each one.

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

### 1. Install and make models reportable

- After `composer require julitom3/cubes`, run `php artisan cubes:install` to publish the config and translations; add `--force` to overwrite published files.
- Add the `Julitom3\Cubes\Traits\Cubable` trait to every Eloquent model reports can use, and implement `cubeSchema(): CubeSchema`.
- Build the schema with `CubeSchema::make()->dimensions([...])->metrics([...])->relations([...])`. A field is a column name (label from the column, format from the model casts) or a `FieldDefinitionDto` for a custom label, format, aggregations, or expression.
- `relations()` takes the names of `BelongsTo`, `HasOne`, or `HasMany` methods of the model, optionally keyed with a `JoinType`; declare each relation once, it is walked in both directions.
- Use `->category()` to name the dataset something other than its table. For SQL views, create a model whose `$table` is the view.
- With `cubes.auto_discover` true, Cubable models in `cubes.discover_paths` are registered automatically; with it false, list them in `cubes.models`.
- Do not write dataset classes or generate them: the Cubable model is the only source.

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

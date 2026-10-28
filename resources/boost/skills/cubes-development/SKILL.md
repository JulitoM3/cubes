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

### 1. Define datasets

- Generate a dataset per table with `php artisan cubes:make-dataset {table}`, or list tables in `cubes.generator.tables` and run `php artisan cubes:sync`.
- Datasets extend `Julitom3\Cubes\Datasets\Dataset` and implement `table()`, `fields()`, and optionally `relations()`.
- Use `Field::dimension()` for fields that rows and columns group by, and `Field::metric()` for fields aggregated as values.
- Declare `new Relation('related_table', 'local_key', 'foreign_key')` so Cubes can resolve joins.
- Register every dataset in `config/cubes.php` under `datasets`.

### 2. Build reports

```php
use Julitom3\Cubes\Facades\Cubes;

Cubes::report()
    ->rows('regions.name')
    ->columns('sales.year')
    ->value('sales.amount', 'sum', 'Total sales')
    ->matrix();
```

- Reference fields as `dataset.field`.
- `get()` returns flat rows, `matrix()` returns a JSON-serializable pivot table, and `toQuery()` returns the query builder.
- For frontends, expose `Cubes::catalog()` and build reports with `Cubes::report($definition)`, where the definition has `from`, `rows`, `columns`, and `values`.

## Anti-patterns

- Do not build report SQL by hand; let Cubes resolve joins from the dataset relations.
- Do not put user input in a field `expression`; expressions are trusted SQL defined in the dataset.
- Do not reuse a report instance for different reports; call `Cubes::report()` for each one.

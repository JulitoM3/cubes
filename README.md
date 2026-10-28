<div align="center">
    <h1>Cubes</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/julitom3/cubes"><img src="https://img.shields.io/packagist/v/julitom3/cubes.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/julitom3/cubes"><img src="https://img.shields.io/packagist/php-v/julitom3/cubes.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/julitom3/cubes"><img src="https://badge.laravel.cloud/badge/julitom3/cubes?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/julitom3/cubes/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/julitom3/cubes/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/julitom3/cubes"><img src="https://img.shields.io/packagist/dt/julitom3/cubes.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A pivot-table report engine for Laravel. Describe your tables as datasets, pick rows, columns, and values, and Cubes resolves the joins, builds the query, and returns a matrix that any frontend (Blade, Livewire, Vue, React, Angular) can render.

## Installation

You can install the package via Composer:

```bash
composer require julitom3/cubes
```

Publish the configuration file, the translations, or the dataset stub:

```bash
php artisan vendor:publish --tag="cubes-config"
php artisan vendor:publish --tag="cubes-lang"
php artisan vendor:publish --tag="cubes-stubs"
```

## Datasets

A dataset describes a table: the fields reports can use and its relations to other datasets.

```php
use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;
use Julitom3\Cubes\Datasets\Relation;
use Julitom3\Cubes\Enums\Aggregation;
use Julitom3\Cubes\Enums\FieldFormat;

class SalesDataset extends Dataset
{
    public function table(): string
    {
        return 'sales';
    }

    public function fields(): array
    {
        return [
            Field::metric('amount', 'Amount', format: FieldFormat::Currency),
            Field::metric('id', 'Sales count', [Aggregation::Count]),
            Field::dimension('year', 'Year', FieldFormat::Year, pivotable: true),
            Field::dimension('month', 'Month', expression: 'MONTH(sales.sold_at)'),
        ];
    }

    public function relations(): array
    {
        return [
            new Relation('stores', 'store_id'), // sales.store_id = stores.id
        ];
    }
}
```

Register your datasets in `config/cubes.php`:

```php
'datasets' => [
    App\Cubes\Datasets\RegionsDataset::class,
    App\Cubes\Datasets\StoresDataset::class,
    App\Cubes\Datasets\SalesDataset::class,
],
```

Dimensions are used as rows and columns; metrics are aggregated as values. A field `expression` is raw SQL that you write in the dataset; it is never taken from report input.

### Generating Datasets

Generate a dataset from the database schema. Columns become fields and single-column foreign keys become relations:

```bash
php artisan cubes:make-dataset sales
php artisan cubes:make-dataset sales --empty   # without fields
php artisan cubes:make-dataset sales --force   # overwrite
```

Or list tables in `cubes.generator.tables` and generate them all. Existing datasets are skipped unless you pass `--force`:

```bash
php artisan cubes:sync
```

## Usage

Reference fields as `dataset.field`. Cubes finds the shortest path between datasets and joins each table once:

```php
use Julitom3\Cubes\Facades\Cubes;

$report = Cubes::report()
    ->rows('regions.name')
    ->columns('sales.year')
    ->value('sales.amount', 'sum', 'Total sales')
    ->value('sales.id', 'count');

$report->get();      // Collection of ['regions.name' => 'North', 'sales.year' => 2024, 'sum(sales.amount)' => 130, ...]
$report->matrix();   // Pivot table with headers (colspan/rowspan) and aligned cells, JSON serializable
$report->toQuery();  // The underlying query builder
```

The base table is the dataset of the first value. Use `from()` to choose it yourself:

```php
Cubes::report()->from('regions')->rows('regions.name')->value('sales.id', 'count');
```

### Building a Frontend

Send the catalog of datasets and fields to your frontend, and build reports from the definition it sends back:

```php
Route::get('/reports/catalog', fn () => Cubes::catalog());

Route::post('/reports/preview', fn (Request $request) => Cubes::report($request->validate([
    'from' => ['nullable', 'string'],
    'rows' => ['array'],
    'columns' => ['array'],
    'values' => ['array'],
]))->matrix());
```

A definition looks like this, and `$report->toArray()` returns the same shape:

```json
{
    "from": null,
    "rows": ["regions.name"],
    "columns": ["sales.year"],
    "values": [{ "field": "sales.amount", "aggregation": "sum", "label": "Total sales" }]
}
```

Fields, aggregations, and datasets are validated against the registered datasets, so a report can only use what your datasets expose.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Cubes! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [JulitoM3](https://github.com/julitom3)
- [All Contributors](../../contributors)

## License

Cubes is open-sourced software licensed under the [MIT license](LICENSE.md).

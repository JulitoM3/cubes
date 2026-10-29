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

<p align="center">
    <a href="#espanol"><strong>Español</strong></a> · <a href="#english"><strong>English</strong></a>
</p>

---

<a id="espanol"></a>

# Español

> [Read this in English](#english)

Un motor de reportes tipo tabla dinámica para Laravel. Describe tus tablas como datasets, elige filas, columnas y valores, y Cubes resuelve los joins, construye la consulta y devuelve una matriz que cualquier frontend (Blade, Livewire, Vue, React, Angular) puede renderizar.

**Contenido:** [Instalación](#es-instalacion) · [Datasets](#es-datasets) · [Relaciones](#es-relaciones) · [Generar datasets](#es-generar) · [Uso](#es-uso) · [Cómo se construye un reporte](#es-construccion) · [Construir un frontend](#es-frontend) · [Créditos y licencia](#es-creditos)

<a id="es-instalacion"></a>

## Instalación

Instala el package con Composer:

```bash
composer require julitom3/cubes
```

Después ejecuta el instalador, que publica el archivo de configuración y las traducciones:

```bash
php artisan cubes:install
php artisan cubes:install --with-stubs   # publica también el stub de los datasets para personalizarlo
php artisan cubes:install --force        # sobrescribe los archivos que ya publicaste
```

También puedes publicar cada recurso por separado:

```bash
php artisan vendor:publish --tag="cubes-config"
php artisan vendor:publish --tag="cubes-lang"
php artisan vendor:publish --tag="cubes-stubs"
```

<a id="es-datasets"></a>

## Datasets

Un dataset describe una tabla: los campos que los reportes pueden usar y sus relaciones con otros datasets.

```php
use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;

class SalesDataset extends DatasetContract
{
    public function table(): string
    {
        return 'sales';
    }

    public function fields(): FieldCollection
    {
        return new FieldCollection([
            FieldDefinitionDto::metric('amount', 'Monto', format: FieldFormat::Currency),
            FieldDefinitionDto::metric('id', 'Número de ventas', [Aggregation::Count]),
            FieldDefinitionDto::dimension('year', 'Año', FieldFormat::Year, pivotable: true),
            FieldDefinitionDto::dimension('month', 'Mes', expression: 'MONTH(sales.sold_at)'),
        ]);
    }

    public function relations(): RelationCollection
    {
        return new RelationCollection([
            new RelationDefinitionDto('stores', 'store_id'), // sales.store_id = stores.id
        ]);
    }
}
```

Registra tus datasets en `config/cubes.php`:

```php
'datasets' => [
    App\Cubes\Datasets\RegionsDataset::class,
    App\Cubes\Datasets\StoresDataset::class,
    App\Cubes\Datasets\SalesDataset::class,
],
```

Las dimensiones se usan como filas y columnas; las métricas se agregan como valores. La `expression` de un campo es SQL que tú escribes en el dataset; nunca se toma de lo que envía el reporte.

<a id="es-relaciones"></a>

### Relaciones

Una `RelationDefinitionDto` describe cómo un dataset se une con otro: `new RelationDefinitionDto($table, $localKey, $foreignKey = 'id', $joinType = JoinType::Left)` significa `{tabla del dataset}.{localKey} = {table}.{foreignKey}`. Las tablas y llaves deben ser identificadores válidos.

```php
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\JoinType;

public function relations(): RelationCollection
{
    return new RelationCollection([
        new RelationDefinitionDto('stores', 'store_id'),                             // left join stores on sales.store_id = stores.id
        new RelationDefinitionDto('products', 'product_id', 'id', JoinType::Inner),  // inner join products on sales.product_id = products.id
    ]);
}
```

- Declara cada relación una sola vez, en cualquiera de los dos lados: Cubes recorre cada relación en ambos sentidos, así que `regions` llega a `sales` a través de `stores` sin declarar la inversa.
- La tabla relacionada debe pertenecer a un dataset registrado; las relaciones hacia otras tablas se ignoran.
- Cuando un reporte usa varios datasets, Cubes sigue el camino más corto de relaciones desde el dataset base y convierte cada paso en un `JoinDefinitionDto`.

<a id="es-generar"></a>

### Generar datasets

Genera un dataset a partir del esquema de la base de datos. Las columnas se vuelven campos y las llaves foráneas de una sola columna se vuelven relaciones:

```bash
php artisan cubes:make-dataset sales
php artisan cubes:make-dataset sales --empty   # sin campos
php artisan cubes:make-dataset sales --force   # sobrescribir
```

O lista las tablas en `cubes.generator.tables` y genéralas todas. Los datasets que ya existen se omiten, a menos que uses `--force`:

```bash
php artisan cubes:sync
```

Todos los campos generados son dimensiones; cambia a `FieldDefinitionDto::metric()` los que quieras agregar como valores.

<a id="es-uso"></a>

## Uso

El usuario arrastra campos desde una paleta hacia filas, columnas y valores. Cada campo soltado es un arreglo con la `key` del campo, y los valores pueden agregar una `aggregation` y un `label` para mostrar. El `ReportEngine` resuelve cada key contra los datasets registrados, encuentra el camino más corto entre ellos y une cada tabla una sola vez:

```php
use Julitom3\Cubes\Facades\Cubes;

$engine = Cubes::engine()
    ->rows([['key' => 'regions.name']])
    ->columns([['key' => 'sales.year']])
    ->values([
        ['key' => 'sales.amount', 'aggregation' => 'sum', 'label' => 'Ventas totales'],
        ['key' => 'sales.id', 'aggregation' => 'count'],
    ]);

$engine->get();           // Colección de ['regions.name' => 'North', 'sales.year' => 2024, 'sum(sales.amount)' => 130, ...]
$engine->matrixRender();  // Tabla dinámica con encabezados (colspan/rowspan) y celdas alineadas, serializable a JSON
$engine->build();         // El query builder compilado
```

`get()` y `matrixRender()` ejecutan la consulta cada vez que los llamas; si necesitas ambos, ten en cuenta que serán dos consultas. Cada engine contiene un solo reporte, así que llama a `Cubes::engine()` para cada uno. La tabla base es el dataset del primer valor. Usa `from()` para elegirla tú:

```php
Cubes::engine()->from('regions')->rows([['key' => 'regions.name']])->values([['key' => 'sales.id', 'aggregation' => 'count']]);
```

<a id="es-construccion"></a>

### Cómo se construye un reporte

`ReportEngine` solo orquesta; cada paso tiene su propia clase y produce un DTO, así que puedes inspeccionarlo o probarlo antes de ejecutar cualquier SQL. Hay dos caminos que comparten el `ReportDefinitionDto`:

```text
rows() / columns() / values()
  └─ FieldsResolver ─► ReportDefinition ─► ReportDefinitionDto
                                              │
       ┌──────────────────────────────────────┴───────────────────────────┐
       ▼ consulta                                                         ▼ matriz
QueryDefinition                                                    MatrixDefinition ─► MatrixDefinitionDto
  ├─ DatasetGraphDefinition   ─► DatasetGraphDefinitionDto           MatrixTreeFactory ─► MatrixLayoutDto
  ├─ DatasetTraversalResolver ─► DatasetRootDto                        (MatrixWalker: árbol de filas y de columnas)
  ├─ DatasetPathDefinition    ─► DatasetPathDto (BFS)                MatrixRenderFactory
  ├─ From/Join/Select/Value/GroupBy/OrderBy DefinitionFactory          ├─ HeaderWalker ─► RowHeaderWalker + ColumnHeaderWalker
  └─► QueryDefinitionDto ─► SqlCompiler ─► Builder                     └─ BodyWalker
                                                                     ─► MatrixRenderDto
```

1. **Campos.** `FieldsResolver` convierte los arreglos en `DimensionDefinitionDto` y `ValueDefinitionDto`, y rechaza las keys y agregaciones que ningún dataset expone. `ReportDefinition` los acumula y `$engine->reportDefinition()` devuelve el `ReportDefinitionDto`.
2. **Grafo.** `DatasetGraphDefinition` arma un nodo (`DatasetNodeDto`) por tabla y una arista (`DatasetEdgeDto`) por relación en cada sentido. En una arista, `localKey` siempre pertenece a la tabla `from` y `foreignKey` a la tabla `to`.
3. **Raíz.** `DatasetTraversalResolver` elige la tabla base: la de `from()`, si no la del primer valor y, si no hay valores, el dataset seleccionado con más relaciones. Las demás tablas seleccionadas son los destinos (`DatasetRootDto`).
4. **Caminos.** `DatasetPathDefinition` busca el camino más corto a cada destino con BFS (`DatasetPathDto`), y `JoinDefinitionFactory` convierte cada par del camino en un `JoinDefinitionDto`, uniendo cada tabla una sola vez y nunca de nuevo la tabla base.
5. **Consulta.** `$engine->queryDefinition()` devuelve el `QueryDefinitionDto`, el contrato de la consulta, con un DTO por cláusula: `FromDefinitionDto`, `SelectDefinitionDto`, `JoinDefinitionDto`, `GroupByDefinitionDto` y `OrderByDefinitionDto`. `SqlCompiler` lo compila con un compilador por cláusula: `FromCompiler`, `JoinCompiler`, `SelectCompiler`, `GroupByCompiler` y `OrderByCompiler`.
6. **Matriz.** `MatrixTreeFactory` arma un árbol de filas y uno de columnas (`MatrixNodeDto`) a partir de los registros. `HeaderWalker` combina los encabezados de `RowHeaderWalker` y `ColumnHeaderWalker`, y `BodyWalker` produce una fila por hoja del árbol de filas, con una celda por hoja de columnas y valor, en el mismo orden que los encabezados.

Para cambiar el SQL que se genera, modifica el DTO de la cláusula, su factory y su compilador; no agregues SQL al query builder fuera de los compiladores.

`$engine->matrixRender()` devuelve un `MatrixRenderDto`, que se serializa a arreglos simples (`toArray()` o JSON) para que cualquier frontend lo dibuje:

```json
{
    "headers": [[{ "label": "Region", "kind": "dimension", "depth": 0, "colspan": 1, "rowspan": 1, "isLeaf": false, "value": null }, ...]],
    "body": { "rows": [{ "headers": [{ "label": "North", "value": "North", ... }], "cells": [{ "value": 130, "format": "currency" }, ...] }] }
}
```

<a id="es-frontend"></a>

### Construir un frontend

Envía a tu frontend la paleta de todos los datasets. Cada campo tiene una `key`, su `dataset`, `label`, `type` (`dimension` o `metric`), `format` y las `aggregations` permitidas; los nombres de tablas y columnas nunca se envían:

```php
Route::get('/reports/catalog', fn () => Cubes::catalog());

Route::post('/reports/preview', function (Request $request) {
    $report = $request->validate([
        'from' => ['nullable', 'string'],
        'rows' => ['array'],
        'columns' => ['array'],
        'values' => ['array'],
    ]);

    return Cubes::engine()
        ->from($report['from'] ?? null)
        ->rows($report['rows'] ?? [])
        ->columns($report['columns'] ?? [])
        ->values($report['values'] ?? [])
        ->matrixRender();
});
```

El frontend puede devolver los elementos de la paleta tal como los soltó el usuario; solo se leen `key`, `aggregation` y `label`. `$engine->reportDefinition()->toArray()` devuelve la misma forma:

```json
{
    "from": null,
    "rows": [{ "key": "regions.name" }],
    "columns": [{ "key": "sales.year" }],
    "values": [{ "key": "sales.amount", "aggregation": "sum", "label": "Ventas totales" }]
}
```

Las keys, agregaciones y datasets se validan contra los datasets registrados, y el SQL se construye solo a partir de las definiciones de los datasets, así que un reporte solo puede usar lo que tus datasets exponen. Los labels se muestran, nunca se compilan. Para no exponer nombres de tablas, sobrescribe `category()` en el dataset y dale un nombre lógico.

<a id="es-creditos"></a>

## Créditos y licencia

- Consulta el [CHANGELOG](CHANGELOG.md) para ver los cambios recientes.
- Revisa la [guía de contribución](.github/CONTRIBUTING.md) antes de contribuir.
- Revisa la [política de seguridad](.github/SECURITY.md) para reportar vulnerabilidades.
- Autor: [JulitoM3](https://github.com/julitom3) y [todos los contribuidores](../../contributors).

Cubes es software de código abierto bajo la [licencia MIT](LICENSE.md).

<p align="right"><a href="#espanol">↑ Volver arriba</a> · <a href="#english">English →</a></p>

---

<a id="english"></a>

# English

> [Leer en español](#espanol)

A pivot-table report engine for Laravel. Describe your tables as datasets, pick rows, columns, and values, and Cubes resolves the joins, builds the query, and returns a matrix that any frontend (Blade, Livewire, Vue, React, Angular) can render.

**Contents:** [Installation](#en-installation) · [Datasets](#en-datasets) · [Relations](#en-relations) · [Generating datasets](#en-generating) · [Usage](#en-usage) · [How a report is built](#en-building) · [Building a frontend](#en-frontend) · [Credits and license](#en-credits)

<a id="en-installation"></a>

## Installation

You can install the package via Composer:

```bash
composer require julitom3/cubes
```

Then run the installer, which publishes the configuration file and the translations:

```bash
php artisan cubes:install
php artisan cubes:install --with-stubs   # also publish the dataset stub to customize it
php artisan cubes:install --force        # overwrite files you already published
```

You can also publish each resource on its own:

```bash
php artisan vendor:publish --tag="cubes-config"
php artisan vendor:publish --tag="cubes-lang"
php artisan vendor:publish --tag="cubes-stubs"
```

<a id="en-datasets"></a>

## Datasets

A dataset describes a table: the fields reports can use and its relations to other datasets.

```php
use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;

class SalesDataset extends DatasetContract
{
    public function table(): string
    {
        return 'sales';
    }

    public function fields(): FieldCollection
    {
        return new FieldCollection([
            FieldDefinitionDto::metric('amount', 'Amount', format: FieldFormat::Currency),
            FieldDefinitionDto::metric('id', 'Sales count', [Aggregation::Count]),
            FieldDefinitionDto::dimension('year', 'Year', FieldFormat::Year, pivotable: true),
            FieldDefinitionDto::dimension('month', 'Month', expression: 'MONTH(sales.sold_at)'),
        ]);
    }

    public function relations(): RelationCollection
    {
        return new RelationCollection([
            new RelationDefinitionDto('stores', 'store_id'), // sales.store_id = stores.id
        ]);
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

<a id="en-relations"></a>

### Relations

A `RelationDefinitionDto` describes how a dataset joins another one: `new RelationDefinitionDto($table, $localKey, $foreignKey = 'id', $joinType = JoinType::Left)` means `{dataset table}.{localKey} = {table}.{foreignKey}`. Tables and keys must be valid identifiers.

```php
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\JoinType;

public function relations(): RelationCollection
{
    return new RelationCollection([
        new RelationDefinitionDto('stores', 'store_id'),                             // left join stores on sales.store_id = stores.id
        new RelationDefinitionDto('products', 'product_id', 'id', JoinType::Inner),  // inner join products on sales.product_id = products.id
    ]);
}
```

- Declare each relation once, on either side: Cubes walks every relation in both directions, so `regions` reaches `sales` through `stores` without declaring the inverse.
- The related table must belong to a registered dataset; relations to other tables are ignored.
- When a report uses several datasets, Cubes follows the shortest path of relations from the base dataset and turns each step into a `JoinDefinitionDto`.

<a id="en-generating"></a>

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

Every generated field is a dimension; change the ones you want to aggregate as values to `FieldDefinitionDto::metric()`.

<a id="en-usage"></a>

## Usage

The user drags fields from a palette onto rows, columns, and values. Each dropped field is an array with the `key` of the field, and values may add an `aggregation` and a display `label`. The `ReportEngine` resolves every key against the registered datasets, finds the shortest path between them, and joins each table once:

```php
use Julitom3\Cubes\Facades\Cubes;

$engine = Cubes::engine()
    ->rows([['key' => 'regions.name']])
    ->columns([['key' => 'sales.year']])
    ->values([
        ['key' => 'sales.amount', 'aggregation' => 'sum', 'label' => 'Total sales'],
        ['key' => 'sales.id', 'aggregation' => 'count'],
    ]);

$engine->get();           // Collection of ['regions.name' => 'North', 'sales.year' => 2024, 'sum(sales.amount)' => 130, ...]
$engine->matrixRender();  // Pivot table with headers (colspan/rowspan) and aligned cells, JSON serializable
$engine->build();         // The compiled query builder
```

`get()` and `matrixRender()` run the query every time you call them; if you need both, that is two queries. Every engine holds a single report, so call `Cubes::engine()` for each one. The base table is the dataset of the first value. Use `from()` to choose it yourself:

```php
Cubes::engine()->from('regions')->rows([['key' => 'regions.name']])->values([['key' => 'sales.id', 'aggregation' => 'count']]);
```

<a id="en-building"></a>

### How a Report Is Built

`ReportEngine` only orchestrates; every step has its own class and produces a DTO, so you can inspect or test it before any SQL runs. There are two paths that share the `ReportDefinitionDto`:

```text
rows() / columns() / values()
  └─ FieldsResolver ─► ReportDefinition ─► ReportDefinitionDto
                                              │
       ┌──────────────────────────────────────┴───────────────────────────┐
       ▼ query                                                            ▼ matrix
QueryDefinition                                                    MatrixDefinition ─► MatrixDefinitionDto
  ├─ DatasetGraphDefinition   ─► DatasetGraphDefinitionDto           MatrixTreeFactory ─► MatrixLayoutDto
  ├─ DatasetTraversalResolver ─► DatasetRootDto                        (MatrixWalker: row and column trees)
  ├─ DatasetPathDefinition    ─► DatasetPathDto (BFS)                MatrixRenderFactory
  ├─ From/Join/Select/Value/GroupBy/OrderBy DefinitionFactory          ├─ HeaderWalker ─► RowHeaderWalker + ColumnHeaderWalker
  └─► QueryDefinitionDto ─► SqlCompiler ─► Builder                     └─ BodyWalker
                                                                     ─► MatrixRenderDto
```

1. **Fields.** `FieldsResolver` turns the arrays into `DimensionDefinitionDto` and `ValueDefinitionDto`, rejecting keys and aggregations that no dataset exposes. `ReportDefinition` collects them and `$engine->reportDefinition()` returns the `ReportDefinitionDto`.
2. **Graph.** `DatasetGraphDefinition` builds one node (`DatasetNodeDto`) per table and one edge (`DatasetEdgeDto`) per relation in each direction. On an edge, `localKey` always belongs to the `from` table and `foreignKey` to the `to` table.
3. **Root.** `DatasetTraversalResolver` chooses the base table: the one given to `from()`, else the one of the first value, and without values the selected dataset with the most relations. The other selected tables are the destinations (`DatasetRootDto`).
4. **Paths.** `DatasetPathDefinition` finds the shortest path to each destination with a BFS (`DatasetPathDto`), and `JoinDefinitionFactory` turns each pair of the path into a `JoinDefinitionDto`, joining each table once and never the base table again.
5. **Query.** `$engine->queryDefinition()` returns the `QueryDefinitionDto`, the contract of the query, with one DTO per clause: `FromDefinitionDto`, `SelectDefinitionDto`, `JoinDefinitionDto`, `GroupByDefinitionDto`, and `OrderByDefinitionDto`. `SqlCompiler` compiles it with one compiler per clause: `FromCompiler`, `JoinCompiler`, `SelectCompiler`, `GroupByCompiler`, and `OrderByCompiler`.
6. **Matrix.** `MatrixTreeFactory` builds a row tree and a column tree (`MatrixNodeDto`) from the records. `HeaderWalker` combines the headers of `RowHeaderWalker` and `ColumnHeaderWalker`, and `BodyWalker` produces one row per row leaf, with one cell per column leaf and value, in header order.

To change the generated SQL, change the DTO of the clause, its factory, and its compiler; do not add SQL to the query builder outside the compilers.

`$engine->matrixRender()` returns a `MatrixRenderDto`, which serializes to plain arrays (`toArray()` or JSON) so any frontend can draw it:

```json
{
    "headers": [[{ "label": "Region", "kind": "dimension", "depth": 0, "colspan": 1, "rowspan": 1, "isLeaf": false, "value": null }, ...]],
    "body": { "rows": [{ "headers": [{ "label": "North", "value": "North", ... }], "cells": [{ "value": 130, "format": "currency" }, ...] }] }
}
```

<a id="en-frontend"></a>

### Building a Frontend

Send the palette of every dataset to your frontend. Each field has a `key`, its `dataset`, `label`, `type` (`dimension` or `metric`), `format`, and allowed `aggregations`; table and column names are never sent:

```php
Route::get('/reports/catalog', fn () => Cubes::catalog());

Route::post('/reports/preview', function (Request $request) {
    $report = $request->validate([
        'from' => ['nullable', 'string'],
        'rows' => ['array'],
        'columns' => ['array'],
        'values' => ['array'],
    ]);

    return Cubes::engine()
        ->from($report['from'] ?? null)
        ->rows($report['rows'] ?? [])
        ->columns($report['columns'] ?? [])
        ->values($report['values'] ?? [])
        ->matrixRender();
});
```

The frontend can send the dropped palette items back as they are; only `key`, `aggregation`, and `label` are read. `$engine->reportDefinition()->toArray()` returns the same shape:

```json
{
    "from": null,
    "rows": [{ "key": "regions.name" }],
    "columns": [{ "key": "sales.year" }],
    "values": [{ "key": "sales.amount", "aggregation": "sum", "label": "Total sales" }]
}
```

Keys, aggregations, and datasets are validated against the registered datasets, and the SQL is built only from the dataset definitions, so a report can only use what your datasets expose. Labels are displayed, never compiled. To avoid exposing table names, override `category()` in a dataset to give it a logical name.

<a id="en-credits"></a>

## Credits and License

- Please see the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.
- Please review the [contributing guide](.github/CONTRIBUTING.md) before contributing.
- Please review the [security policy](.github/SECURITY.md) on how to report security vulnerabilities.
- Author: [JulitoM3](https://github.com/julitom3) and [all contributors](../../contributors).

Cubes is open-sourced software licensed under the [MIT license](LICENSE.md).

<p align="right"><a href="#english">↑ Back to top</a> · <a href="#espanol">← Español</a></p>

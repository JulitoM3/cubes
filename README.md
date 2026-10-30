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

**Contenido:** [Instalación](#es-instalacion) · [Modelos reportables](#es-datasets) · [Relaciones](#es-relaciones) · [Registrar los modelos](#es-generar) · [Uso](#es-uso) · [Cómo se construye un reporte](#es-construccion) · [Construir un frontend](#es-frontend) · [Créditos y licencia](#es-creditos)

<a id="es-instalacion"></a>

## Instalación

Instala el package con Composer:

```bash
composer require julitom3/cubes
```

Después ejecuta el instalador, que publica el archivo de configuración y las traducciones:

```bash
php artisan cubes:install
php artisan cubes:install --force   # sobrescribe los archivos que ya publicaste
```

También puedes publicar cada recurso por separado:

```bash
php artisan vendor:publish --tag="cubes-config"
php artisan vendor:publish --tag="cubes-lang"
```

<a id="es-datasets"></a>

## Modelos reportables

Cada modelo Eloquent que usa el trait `Cubable` es un dataset. En `cubeSchema()` declaras qué columnas son dimensiones, cuáles son métricas y qué relaciones del modelo conectan con otros modelos `Cubable`. El modelo es la única fuente: no hay clases de dataset que mantener aparte.

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Datasets\DTOs\Metric;
use Julitom3\Cubes\Datasets\DTOs\Relation;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Traits\Cubable;

class Sale extends Model
{
    use Cubable;

    protected $casts = ['amount' => 'decimal:2', 'sold_at' => 'datetime'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cubeSchema(): CubeSchema
    {
        return CubeSchema::make()
            ->dimensions(
                new Dimension('payment_method', 'Método de pago'),
                new Dimension('year', 'Año', FieldFormat::Year, pivotable: true, expression: 'YEAR(sales.sold_at)'),
            )
            ->metrics(
                new Metric('amount', 'Monto'),
                new Metric('id', 'Número de ventas', [Aggregation::Count]),
            )
            ->relations(new Relation('store'));
    }
}
```

- **`Dimension`** es un campo por el que se agrupan filas y columnas: `new Dimension(columna, etiqueta, formato, pivotable, expression)`. Como valor solo se puede contar.
- **`Metric`** es un campo numérico que se agrega como valor: `new Metric(columna, etiqueta, agregaciones, formato, expression)`. Por defecto permite `sum`, `avg`, `min` y `max`; la primera de la lista es la que se usa si el reporte no pide otra.
- **Solo la columna es obligatoria.** La etiqueta por defecto sale del nombre de la columna (`payment_method` → `Payment Method`). Si no indicas formato, se toma de los `$casts` del modelo (`decimal:2` → decimal, `datetime` → fecha y hora); sin cast, las dimensiones son texto y las métricas enteros.
- **La `expression`** reemplaza a la columna en el SQL; el nombre de la columna queda como identificador del campo (`sales.year`). Es SQL que tú escribes en el modelo; nunca se toma de lo que envía el reporte.
- **El dataset se llama como la tabla** (`sales.amount`). Usa `->category('ventas')` para darle otro nombre y no exponer el de la tabla, y `->label('Ventas')` para su etiqueta.
- **Las vistas SQL también funcionan:** crea un modelo cuyo `$table` sea la vista.

<a id="es-relaciones"></a>

### Relaciones

En `->relations()` pasas un `Relation` por cada método de relación del modelo. Cubes lee las llaves de la propia relación, así que no hace falta que la base de datos tenga llaves foráneas declaradas:

```php
->relations(
    new Relation('store'),                    // left join stores on sales.store_id = stores.id
    new Relation('product', JoinType::Inner), // inner join products on sales.product_id = products.id
)
```

- Se aceptan relaciones `BelongsTo`, `HasOne` y `HasMany`. Las uniones son `left join`, salvo que indiques otro `JoinType`.
- Declara cada relación una sola vez, en cualquiera de los dos modelos: Cubes recorre cada relación en ambos sentidos, así que `regions` llega a `sales` a través de `stores` sin declarar la inversa.
- El modelo relacionado también debe ser `Cubable`; las relaciones hacia otros modelos se ignoran.
- Cuando un reporte usa varios datasets, Cubes sigue el camino más corto de relaciones desde el dataset base y convierte cada paso en un `JoinDefinitionDto`.

<a id="es-generar"></a>

### Registrar los modelos

En `config/cubes.php` eliges cómo encuentra Cubes tus modelos:

```php
'auto_discover' => true,                  // busca los modelos Cubable en discover_paths
'discover_paths' => [app_path('Models')],
'models' => [],                           // con auto_discover en false, solo se registran estos
```

- Con `auto_discover` en `true`, Cubes revisa los archivos PHP de `discover_paths`, incluidas las subcarpetas, y registra cada modelo que use `Cubable`.
- Con `auto_discover` en `false`, solo se registran los modelos que listes en `models`.
- Los modelos se descubren la primera vez que un reporte los necesita, no en cada request.
- También puedes registrar un modelo en tiempo de ejecución con `Cubes::register(Sale::class)`.
- `php artisan cubes:list` muestra los modelos registrados con su dataset, tabla, número de dimensiones y métricas, y relaciones. Si un modelo está mal definido, el error aparece ahí.

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

**Contents:** [Installation](#en-installation) · [Reportable models](#en-datasets) · [Relations](#en-relations) · [Registering models](#en-generating) · [Usage](#en-usage) · [How a report is built](#en-building) · [Building a frontend](#en-frontend) · [Credits and license](#en-credits)

<a id="en-installation"></a>

## Installation

You can install the package via Composer:

```bash
composer require julitom3/cubes
```

Then run the installer, which publishes the configuration file and the translations:

```bash
php artisan cubes:install
php artisan cubes:install --force   # overwrite files you already published
```

You can also publish each resource on its own:

```bash
php artisan vendor:publish --tag="cubes-config"
php artisan vendor:publish --tag="cubes-lang"
```

<a id="en-datasets"></a>

## Reportable Models

Every Eloquent model that uses the `Cubable` trait is a dataset. In `cubeSchema()` you declare which columns are dimensions, which are metrics, and which relations of the model connect it with other `Cubable` models. The model is the only source: there are no separate dataset classes to keep in sync.

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Julitom3\Cubes\Datasets\CubeSchema;
use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Datasets\DTOs\Metric;
use Julitom3\Cubes\Datasets\DTOs\Relation;
use Julitom3\Cubes\Datasets\Enums\Aggregation;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Traits\Cubable;

class Sale extends Model
{
    use Cubable;

    protected $casts = ['amount' => 'decimal:2', 'sold_at' => 'datetime'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cubeSchema(): CubeSchema
    {
        return CubeSchema::make()
            ->dimensions(
                new Dimension('payment_method', 'Payment method'),
                new Dimension('year', 'Year', FieldFormat::Year, pivotable: true, expression: 'YEAR(sales.sold_at)'),
            )
            ->metrics(
                new Metric('amount', 'Amount'),
                new Metric('id', 'Sales count', [Aggregation::Count]),
            )
            ->relations(new Relation('store'));
    }
}
```

- **`Dimension`** is a field that rows and columns are grouped by: `new Dimension(column, label, format, pivotable, expression)`. As a value it can only be counted.
- **`Metric`** is a numeric field aggregated as a value: `new Metric(column, label, aggregations, format, expression)`. It allows `sum`, `avg`, `min`, and `max` by default; the first one in the list is used when the report does not ask for another.
- **Only the column is required.** The label defaults to the column name (`payment_method` → `Payment Method`). Without a format, it comes from the model `$casts` (`decimal:2` → decimal, `datetime` → date and time); without a cast, dimensions are text and metrics are integers.
- **The `expression`** replaces the column in the SQL; the column name stays as the identifier of the field (`sales.year`). It is SQL that you write in the model; it is never taken from report input.
- **The dataset is named after the table** (`sales.amount`). Use `->category('revenue')` to name it otherwise and keep the table name private, and `->label('Revenue')` for its label.
- **SQL views work too:** create a model whose `$table` is the view.

<a id="en-relations"></a>

### Relations

Pass a `Relation` for each relation method of the model to `->relations()`. Cubes reads the keys from the relation itself, so the database does not need declared foreign keys:

```php
->relations(
    new Relation('store'),                    // left join stores on sales.store_id = stores.id
    new Relation('product', JoinType::Inner), // inner join products on sales.product_id = products.id
)
```

- `BelongsTo`, `HasOne`, and `HasMany` relations are supported. Joins are left joins unless you give another `JoinType`.
- Declare each relation once, on either model: Cubes walks every relation in both directions, so `regions` reaches `sales` through `stores` without declaring the inverse.
- The related model must be `Cubable` too; relations to other models are ignored.
- When a report uses several datasets, Cubes follows the shortest path of relations from the base dataset and turns each step into a `JoinDefinitionDto`.

<a id="en-generating"></a>

### Registering Models

In `config/cubes.php` you choose how Cubes finds your models:

```php
'auto_discover' => true,                  // find the Cubable models in discover_paths
'discover_paths' => [app_path('Models')],
'models' => [],                           // with auto_discover false, only these are registered
```

- With `auto_discover` set to `true`, Cubes reads the PHP files in `discover_paths`, subdirectories included, and registers every model that uses `Cubable`.
- With `auto_discover` set to `false`, only the models listed in `models` are registered.
- Models are discovered the first time a report needs them, not on every request.
- You can also register a model at runtime with `Cubes::register(Sale::class)`.
- `php artisan cubes:list` shows the registered models with their dataset, table, number of dimensions and metrics, and relations. A model that is defined wrong fails there.

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

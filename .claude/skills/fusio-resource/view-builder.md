# View + `PSX\Nested\Builder` reference

Views live in `src/View/<Entity>.php` and extend `PSX\Sql\ViewAbstract`, which provides `$this->connection` (Doctrine
DBAL) and `$this->getTable(Table\X::class)`. Reference: `src/View/Todo.php`.

The builder turns a nested PHP array definition into the JSON response. Keys are output property names (camelCase,
matching the TypeSchema). Values are column names or field and provider objects.

## Providers (data sources)

Each `do*` method accepts as `$source`:
- a callable `[$table, 'method']` with `$arguments` (preferred, since it uses the generated table methods)
- a raw SQL string with `$arguments` as bound parameters (for joins and aggregates)
- an array (static data)

| Method | Produces |
|--------|----------|
| `doCollection($source, $args, $definition)` | a list of objects |
| `doEntity($source, $args, $definition)` | a single object (or `null`) |
| `doColumn($source, $args, $column)` | a flat list of one column's values |
| `doValue($source, $args, $column)` | a single scalar |

Use `new PSX\Nested\Reference('column_name')` as an argument to pass a value from the **parent row**. This is how you
nest relations (e.g. the todo's `user`).

## Field types

A plain string value copies the column as is. Wrap it to cast:

| Method | Purpose |
|--------|---------|
| `fieldInteger($col)` | cast to int (always use for IDs) |
| `fieldNumber($col)` | cast to float |
| `fieldBoolean($col)` | cast to bool (e.g. `completed` stored as 0 or 1) |
| `fieldDateTime($col)` | format as an RFC 3339 date-time |
| `fieldJson($col)` | decode a JSON column |
| `fieldCsv($col, ',')` | split a CSV column into an array |
| `fieldFormat($col, '/product/%s')` | sprintf with the column value (links) |
| `fieldValue($value)` | a constant value |
| `fieldCallback($col, fn($value) => ...)` | custom transformation |

## Collection vs. entity response

`getCollection` (`GET /product`) and `getEntity` (`GET /product/:id`) deliberately use **separate, inline
definitions**. Don't extract a shared `getDefinition()` helper.

- **Collection (list)**: only the most important properties needed to show the item in a list (ID, name or title,
  status, date). Leave out large text fields and expensive nested lookups, because they run for every row.
- **Entity (detail)**: the full representation, with all fields, nested relations, and related collections.

Both responses can use the same TypeSchema `<Entity>` struct, because all generated properties are optional. The
list simply fills fewer of them. Only define a separate summary struct if the shapes really differ.

## Template

```php
public function getCollection(int $startIndex, int $count, ?string $search = null): mixed
{
    if (empty($startIndex) || $startIndex < 0) {
        $startIndex = 0;
    }

    if (empty($count) || $count < 1 || $count > 1024) {
        $count = 16;
    }

    $condition = Condition::withAnd();
    if ($search !== null && $search !== '') {
        $condition->like(Table\Generated\ProductTable::COLUMN_NAME, '%' . $search . '%');
    }

    $builder = new Builder($this->connection);

    $definition = [
        'totalResults' => $this->getTable(Table\Product::class)->getCount($condition),
        'startIndex' => $startIndex,
        'itemsPerPage' => $count,
        // list response: only the properties needed to render a list
        'items' => $builder->doCollection([$this->getTable(Table\Product::class), 'findAll'], [$condition, $startIndex, $count], [
            'id' => $builder->fieldInteger(Table\Generated\ProductTable::COLUMN_ID),
            'name' => Table\Generated\ProductTable::COLUMN_NAME,
            'price' => $builder->fieldNumber(Table\Generated\ProductTable::COLUMN_PRICE),
            'insertDate' => $builder->fieldDateTime(Table\Generated\ProductTable::COLUMN_INSERT_DATE),
        ]),
    ];

    return $builder->build($definition);
}

public function getEntity(int $id): mixed
{
    $builder = new Builder($this->connection);

    // detail response: all properties, including large fields and nested relations
    $definition = $builder->doEntity([$this->getTable(Table\Product::class), 'find'], [$id], [
        'id' => $builder->fieldInteger(Table\Generated\ProductTable::COLUMN_ID),
        'user' => $builder->doEntity([$this->getTable(UserTable::class), 'find'], [new Reference(Table\Generated\ProductTable::COLUMN_USER_ID)], [
            'id' => $builder->fieldInteger(UserTable::COLUMN_ID),
            'name' => UserTable::COLUMN_NAME,
        ]),
        'name' => Table\Generated\ProductTable::COLUMN_NAME,
        'description' => Table\Generated\ProductTable::COLUMN_DESCRIPTION,
        'price' => $builder->fieldNumber(Table\Generated\ProductTable::COLUMN_PRICE),
        'active' => $builder->fieldBoolean(Table\Generated\ProductTable::COLUMN_ACTIVE),
        'insertDate' => $builder->fieldDateTime(Table\Generated\ProductTable::COLUMN_INSERT_DATE),
    ]);

    $entity = $builder->build($definition);
    if (empty($entity)) {
        throw new StatusCode\NotFoundException('Provided product does not exist');
    }

    return $entity;
}
```

`PSX\Sql\Condition` supports `equals`, `notEquals`, `greater`, `greaterThan`, `less`, `lessThan`, `like`, `notLike`,
`between`, `in`, `notIn`, `nil`, `notNil`, and `raw`. Use `Condition::withAnd()` or `Condition::withOr()`.

Notes:
- Always throw `PSX\Http\Exception\NotFoundException` when the entity doesn't exist (see the template), so the client
  gets a 404 instead of an empty body.
- To return data owned by the current user, add `$condition->equals(COLUMN_USER_ID, $userId)` and pass the user ID in
  from the action (`$context->getUser()->getId()`).
- Fusio tables (e.g. users) can be joined through `Fusio\Impl\Table\Generated\UserTable`, as `src/View/Todo.php`
  does.

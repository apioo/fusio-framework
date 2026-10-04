---
name: fusio-migration
description: Create or change database tables in this Fusio project with a Doctrine migration, then regenerate the type-safe table classes in src/Table. Use when the user needs a new table, column, index, or relation.
---

# Database migration + table classes

## 1. Generate the migration

```
php bin/fusio migrations:generate --no-interaction
```

This creates `src/Migrations/Version<timestamp>.php` (namespace `App\Migrations`). Open the newest file and implement
it. Reference: `src/Migrations/Version20260510082730.php`.

## 2. Write `up()` (and `down()`)

```php
public function getDescription(): string
{
    return 'Adds the product table';
}

public function up(Schema $schema): void
{
    $table = $schema->createTable('app_product');
    $table->addColumn('id', 'integer', ['autoincrement' => true]);
    $table->addColumn('user_id', 'integer');
    $table->addColumn('name', 'string', ['length' => 255]);
    $table->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2]);
    $table->addColumn('description', 'text', ['notnull' => false]);
    $table->addColumn('active', 'integer', ['default' => 1]);
    $table->addColumn('insert_date', 'datetime');
    $table->setPrimaryKey(['id']);
    $table->addIndex(['user_id']);

    $table->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'product_user_id');
}

public function down(Schema $schema): void
{
    $schema->dropTable('app_product');
}

public function isTransactional(): bool
{
    return false;
}
```

Rules:
- **Table names must start with `app_`.** `generate:table` only picks up `app_*` tables, and `fusio_*` is reserved
  for Fusio.
- Column names are snake_case. Every table gets an `id` integer autoincrement primary key.
- Store booleans as `integer` (0 or 1), as the todo table does, and cast them with `fieldBoolean` in views.
- Foreign keys to users point to `fusio_user.id`. Give constraints a unique name.
- To change an existing table, **never edit a migration that has already run**. Generate a new one and use
  `$schema->getTable('app_x')->addColumn(...)` / `modifyColumn(...)` / `dropColumn(...)`.
- Keep `isTransactional(): false` (MySQL DDL isn't transactional).
- Doctrine column types: `integer`, `bigint`, `smallint`, `string`, `text`, `boolean`, `decimal`, `float`, `date`,
  `datetime`, `time`, `json`, `guid`, `binary`, `blob`.

## 3. Run the migration (ASK FIRST)

The migration changes the database the user configured in `.env` (`FUSIO_CONNECTION`). **Always ask** before you run:

```
php bin/fusio migrations:migrate --no-interaction
```

Explain that the table classes can't be generated until the migration has run. `php bin/fusio migrations:status`
shows what is pending.

## 4. Generate the table classes

```
php bin/fusio generate:table
```

For `app_product` this writes:
- `src/Table/Generated/ProductTable.php`, `ProductRow.php`, `ProductColumn.php`. These are always overwritten, so
  never edit them.
- `src/Table/Product.php` (extends `ProductTable`). It is created only if missing, so put custom query methods here.

The generated table provides `find($id)`, `findAll($condition, $startIndex, $count, $sortBy, $sortOrder)`,
`findBy(...)`, `findOneBy(...)`, `findBy<Column>(...)`, `findOneBy<Column>(...)`, `getCount($condition)`,
`create($row)`, `update($row)`, `delete($row)`, and `getLastInsertId()`. Rows have typed getters and setters
(`setInsertDate(LocalDateTime::now())`). Column names are available as `ProductTable::COLUMN_*`.

Inject `App\Table\Product` (not the generated class) into services and views.

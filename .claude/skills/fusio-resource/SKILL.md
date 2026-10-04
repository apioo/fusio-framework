---
name: fusio-resource
description: Add a complete REST resource (CRUD endpoints) to this Fusio project. Covers TypeSchema DTOs, DB migration, table classes, view, service, actions, operation configs, scopes, roles, events, and deploy. Use whenever the user wants a new entity, endpoint, route, or API feature.
---

# Add a Fusio resource

Build the resource in the order below, because each step depends on the output of the previous one. The `todo`
resource is the reference. Read the matching todo file before you write each new file, and mirror its structure.

Throughout, `<Entity>` is the PascalCase singular name (`Product`) and `<entity>` is lowercase (`product`).

## 0. Clarify

Before you write code, confirm the fields (name, type, required?), the relations (e.g. owned by the current user via
`user_id`), which endpoints are needed (often the full CRUD set), which endpoints are public, and which events matter.
If the request is clear, go ahead with sensible defaults: full CRUD, GET public, writes private.

## 1. Schema: `resources/typeschema.json`

Add the definitions to `definitions`:
- `<Entity>`: a `struct` with the payload fields. Include `id` (integer) so responses can return it.
- `<Entity>_Collection`: a struct whose `parent` references `Collection` with template `{"T": "<Entity>"}`.
  Copy `Todo_Collection`.
- Reuse the existing `Message` (write responses) and `Collection` definitions. Don't redefine them.

See [typeschema.md](typeschema.md) for the type syntax. Then run:

```
php bin/fusio generate:model
```

This writes `src/Model/<Entity>.php` and `src/Model/<Entity>Collection.php` (`_` is dropped). Never edit them.

## 2. Database: migration + tables

Follow the `/fusio-migration` skill. In short:
1. `php bin/fusio migrations:generate --no-interaction`
2. Fill in `up()` in the new `src/Migrations/VersionXXX.php`. Use the table name `app_<entity>` (the `app_` prefix
   is required).
3. **Ask the user** whether to run `php bin/fusio migrations:migrate --no-interaction` now. The table classes can only
   be generated after the tables exist.
4. `php bin/fusio generate:table`. This creates `src/Table/Generated/<Entity>{Table,Row,Column}.php` and
   `src/Table/<Entity>.php`.

If the user declines the migration, still write the view and service against the expected class names
(`App\Table\<Entity>`, `App\Table\Generated\<Entity>Row`, `<Entity>Table::COLUMN_<NAME>`) and tell them which commands
to run before the code will work.

## 3. View: `src/View/<Entity>.php` (read side)

Extend `PSX\Sql\ViewAbstract`, with `getCollection(int $startIndex, int $count, ?string $search = null)` and
`getEntity(int $id)`. Use `PSX\Nested\Builder` and the `COLUMN_*` constants. Each method has its own inline
definition: the collection returns only the key properties for a list, and the entity returns the full detail
representation. Don't share one definition between them. The output keys must match the
TypeSchema property names (camelCase), and the collection shape must match `Collection`
(`totalResults`, `startIndex`, `itemsPerPage`, `items`). See [view-builder.md](view-builder.md).

## 4. Service: `src/Service/<Entity>.php` (write side)

A `readonly` class with constructor injection (`App\Table\<Entity>`, `Fusio\Engine\DispatcherInterface`, plus
anything else needed). It needs these methods:
- `create(Model\<Entity> $payload, ContextInterface $context): int`: validate, build a `Generated\<Entity>Row`,
  `$this->table->create($row)`, `$id = $this->table->getLastInsertId()`, dispatch `<entity>_created`, return the ID.
- `update(int $id, Model\<Entity> $payload): int`: `find($id)` or throw `NotFoundException`, apply the changed
  fields, `update($row)`, dispatch `<entity>_updated`.
- `delete(int $id): int`: `find($id)` or throw `NotFoundException`, `delete($row)`, dispatch `<entity>_deleted`.

Validate with `PSX\Http\Exception\BadRequestException`. Copy the private `dispatchEvent()` helper from
`src/Service/Todo.php` (CloudEvents builder, source `/<entity>/<id>`). If records belong to users, check ownership
against `$context->getUser()->getId()` and throw `ForbiddenException`.

## 5. Actions: `src/Action/<Entity>/`

Each action is a `readonly` class that implements `Fusio\Engine\ActionInterface`, has a short docblock, and has one
`handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)` method.

| Action | Delegates to | Returns |
|--------|-------------|---------|
| `GetAll` | `$view->getCollection((int) $request->get('startIndex'), (int) $request->get('count'), $request->get('search'))` | `mixed` |
| `Get` | `$view->getEntity((int) $request->get('id'))` | `mixed` |
| `Create` | `$service->create($request->getPayload(), $context)` | `$this->response->build(201, [], $message)` (inject `Fusio\Engine\Response\FactoryInterface`) |
| `Update` | `$service->update((int) $request->get('id'), $request->getPayload())` | `Model\Message` |
| `Delete` | `$service->delete((int) $request->get('id'))` | `Model\Message` |

Write actions return a `Message` with `success`, `message`, and `id`. Actions contain no business logic.

## 6. Operations: `resources/operations/<entity>/*.php`

Create `collection.php`, `entity.php`, `create.php`, `update.php`, and `delete.php`, then register them in
`resources/operation.yaml`:

```yaml
"<entity>.getAll": !include resources/operations/<entity>/collection.php
"<entity>.get": !include resources/operations/<entity>/entity.php
"<entity>.create": !include resources/operations/<entity>/create.php
"<entity>.update": !include resources/operations/<entity>/update.php
"<entity>.delete": !include resources/operations/<entity>/delete.php
```

See [operation.md](operation.md) for the builder API and the correct incoming and outgoing schema for each verb.

## 7. Scope, role, events

- `resources/scope.yaml`: add `<entity>:` with a `description`.
- `resources/role.yaml`: add the scope to `Administrator`, and also to `Consumer` if regular users should use it.
  Only append scopes, and never remove the existing default scopes (`authorization`, `backend`, `consumer`,
  `default`). Deploy replaces a role's whole scope list.
- `resources/event.yaml`: add `<entity>_created`, `<entity>_updated`, and `<entity>_deleted` with descriptions. The
  names must match what the service dispatches, character for character.

## 8. Verify and deploy

1. `php -l` every new PHP file, and run `vendor/bin/phpstan` if available.
2. Delete the compiled DI container (`rm cache/container.php*`). It isn't rebuilt automatically, so new or changed
   actions, services, and views would otherwise fail with a constructor `TypeError` or "service not found".
3. Run `php bin/fusio deploy` (see `/fusio-deploy`), then `php bin/fusio route` to confirm the routes exist.
4. Test every endpoint with `php bin/fusio serve` (see "Testing endpoints with `serve`" in `CLAUDE.md`): create,
   list, get, update, and delete, plus a 400 (invalid payload), a 404 (unknown ID), and an anonymous write (must be
   rejected with a 401). Check the status line on stderr: create returns 201, and the other successful calls 200. If the deploy created the new scope, ask the user to run `php bin/fusio login` again first. Remove the
   test records afterwards.
5. Summarize for the user the new endpoints (method, path, public or private, scope) and any commands they still need
   to run.

## Checklist

- [ ] typeschema.json definitions + `generate:model`
- [ ] migration (`app_` prefix) + migrate (user approved) + `generate:table`
- [ ] View with getCollection and getEntity
- [ ] Service with create, update, delete + event dispatch
- [ ] Actions GetAll, Get, Create, Update, Delete
- [ ] Operation files + operation.yaml entries
- [ ] scope.yaml, role.yaml, event.yaml
- [ ] clear `cache/container.php*`, deploy
- [ ] test all endpoints with `php bin/fusio serve`

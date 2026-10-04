# TypeSchema reference (`resources/typeschema.json`)

Spec: https://typeschema.org/. All definitions live under the top-level `"definitions"` object. After any change, run
`php bin/fusio generate:model`. Each definition becomes `src/Model/<Name>.php` (underscores are removed:
`Todo_Collection` becomes `TodoCollection`).

## Definition types

```json
"Product": {
  "description": "Represents a product",
  "type": "struct",
  "properties": { ... }
}
```

- `struct`: an object with fixed `properties`. Supports `parent` (inheritance), `base: true` (abstract), and
  `discriminator` + `mapping` (polymorphism).
- `map`: a dictionary whose values all use `schema`, e.g. `{"type": "map", "schema": {"type": "string"}}`.
- `array`: a top-level list, e.g. `{"type": "array", "schema": {"type": "reference", "target": "Product"}}`.

## Property types

| Type | Example |
|------|---------|
| string | `{"type": "string"}` |
| string with format | `{"type": "string", "format": "date-time"}`. Formats: `date`, `date-time`, `time`. These map to `PSX\DateTime\Local*` types |
| integer | `{"type": "integer"}` |
| number (float) | `{"type": "number"}` |
| boolean | `{"type": "boolean"}` |
| any | `{"type": "any"}` |
| reference | `{"type": "reference", "target": "User"}` |
| array | `{"type": "array", "schema": {"type": "reference", "target": "Tag"}}` |
| map | `{"type": "map", "schema": {"type": "integer"}}` |
| generic | `{"type": "generic", "name": "T"}` (only inside templated parents) |

Properties can also have `description`, `nullable`, and `deprecated`.

## Collections

Use the shared generic `Collection` definition through inheritance:

```json
"Product_Collection": {
  "description": "A collection of all products",
  "type": "struct",
  "parent": {
    "type": "reference",
    "target": "Collection",
    "template": { "T": "Product" }
  }
}
```

## Polymorphism

```json
"Payment": {
  "type": "struct",
  "base": true,
  "discriminator": "type",
  "mapping": { "Card": "card", "Paypal": "paypal" },
  "properties": { "type": {"type": "string"} }
},
"Card": { "type": "struct", "parent": {"type": "reference", "target": "Payment"}, "properties": { ... } }
```

## Conventions

- Property names are camelCase (`insertDate`), even though the DB columns are snake_case (`insert_date`).
- Use one DTO for both incoming and outgoing payloads unless the shapes really differ. Read-only fields such as `id`
  or `insertDate` are simply ignored on input.
- Use the existing `Message` for write responses and errors, and `Collection` for lists.

# Operation config reference (`resources/operations/<entity>/*.php`)

Each file returns a closure that configures a `Fusio\Cli\Builder\Operation`. It must be registered in
`resources/operation.yaml` as `"<entity>.<verb>": !include resources/operations/<entity>/<file>.php`.

```php
<?php

use App\Action;
use App\Model;
use Fusio\Cli\Builder\Operation;
use Fusio\Cli\Builder\Operation\HttpMethod;
use Fusio\Cli\Builder\Operation\Stability;
use PSX\Schema\Type\Factory\PropertyTypeFactory;

return function (Operation $operation) {
    $operation->setScopes(['product']);              // must exist in scope.yaml
    $operation->setStability(Stability::EXPERIMENTAL); // EXPERIMENTAL | STABLE | DEPRECATED | LEGACY
    $operation->setPublic(true);                     // true = no access token required
    $operation->setDescription('Returns all available products');
    $operation->setHttpMethod(HttpMethod::GET);      // GET | POST | PUT | PATCH | DELETE
    $operation->setHttpPath('/product');             // path params: '/product/:id'
    $operation->setHttpCode(200);
    $operation->addParameter('startIndex', PropertyTypeFactory::getInteger()); // query params
    $operation->setOutgoing(Model\ProductCollection::class);
    $operation->addThrow(500, Model\Message::class);
    $operation->setAction(Action\Product\GetAll::class);
};
```

Query parameter types: `PropertyTypeFactory::getString()`, `getInteger()`, `getNumber()`, `getBoolean()`,
`getDate()`, and `getDateTime()`.

## Standard CRUD set

| File | ID | Method | Path | Code | Public | Incoming | Outgoing | Action |
|------|----|--------|------|------|--------|----------|----------|--------|
| collection.php | `<entity>.getAll` | GET | `/<entity>` | 200 | yes | – | `<Entity>Collection` | `GetAll` |
| entity.php | `<entity>.get` | GET | `/<entity>/:id` | 200 | yes | – | `<Entity>` | `Get` |
| create.php | `<entity>.create` | POST | `/<entity>` | 201 | no | `<Entity>` | `Message` | `Create` |
| update.php | `<entity>.update` | PUT | `/<entity>/:id` | 200 | no | `<Entity>` | `Message` | `Update` |
| delete.php | `<entity>.delete` | DELETE | `/<entity>/:id` | 200 | no | – | `Message` | `Delete` |

- `collection.php` declares the query params `startIndex` (int), `count` (int), and `search` (string).
- Error responses (`addThrow`) use `Model\Message`. Add 400 and 404 throws when the action can raise them.
- The outgoing schema must describe what the action really returns. A create action returns a `Message`, not the
  collection.
- Make GET endpoints private (`setPublic(false)`) when the data is user specific or sensitive.

---
name: fusio-deploy
description: Apply the metadata in resources/* (operations, scopes, roles, events, cronjobs, config) to the Fusio instance with the deploy command. Use after changing any file in resources/ or when the user asks to deploy.
---

# Deploy

`resources/*` only contains metadata. Nothing changes in the running Fusio instance until you run:

```
php bin/fusio deploy
```

The command reads `.fusio.yml`, which `!include`s `config.yaml`, `cronjob.yaml`, `event.yaml`, `operation.yaml`,
`role.yaml`, and `scope.yaml`. It then submits everything through Fusio's internal REST API, exactly as the backend UI
would. So the whole Fusio configuration lives in Git and can be reproduced anywhere.

## Before deploying

1. `php -l` the changed PHP files (operation configs and actions). A fatal error stops the deploy.
2. Check cross-references:
   - every operation's scope exists in `scope.yaml`
   - every scope used by an app user is in the `Consumer` role in `role.yaml`
   - every event name dispatched in `src/Service/*` is a key in `event.yaml`
   - every `setIncoming` / `setOutgoing` / `addThrow` class exists in `src/Model` (rerun `generate:model` if not)
   - every action class referenced in operations and cronjobs exists
3. Deploy requires an authenticated CLI session. If it fails with an auth error, the user must run
   `php bin/fusio login` (an interactive login, so suggest `! php bin/fusio login`).
4. Before you deploy, ask the user if `.env` / `FUSIO_URL` points to anything other than a local or dev instance.

## After deploying

- `php bin/fusio route` lists the routes that are now active.
- If the deploy reports an error, fix the referenced resource file and deploy again. Deploy is idempotent.
- To enable an optional section (`connection`, `plan`, `agent`), create `resources/<name>.yaml` and uncomment the
  matching line in `.fusio.yml`.

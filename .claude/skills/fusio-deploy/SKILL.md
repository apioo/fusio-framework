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

## Prerequisite: an authenticated CLI session

`deploy` talks to the Fusio API, so the CLI must be logged in. The chain is **adduser, then login, then deploy**:

1. **Check the login state:** `php bin/fusio whoami`. It prints the current user, or `null` if nobody is logged in.
   The login token is stored in `fusio_token.json` and expires (after 2 days by default, see
   `fusio_expire_token` in `configuration.php`). An expired token also requires a new login.
2. **If not logged in, make sure a user exists.** Login only works with an existing account. On a fresh installation
   (right after `migrations:migrate`) there is no user yet, so the user must create an administrator first:

   ```
   php bin/fusio adduser
   ```

   Choose role `1` (Administrator). Deploy needs admin rights.
3. **Log in** with those credentials:

   ```
   php bin/fusio login
   ```

4. **Deploy:** `php bin/fusio deploy`.

`adduser` and `login` prompt for a username, email, and password. **Don't run them yourself and never ask for or
handle the password.** Ask the user to run them in the session with the `!` prefix:

- `! php bin/fusio adduser`
- `! php bin/fusio login`

If you aren't sure whether an account exists, ask the user. They can try `login` first and fall back to `adduser` if
it fails. If `deploy` fails with an auth or token error (e.g. "Existing token is expired"), stop and ask the user to
run `! php bin/fusio login` again, then retry the deploy.

## Before deploying

1. `php -l` the changed PHP files (operation configs and actions). A fatal error stops the deploy.
2. Check cross-references:
   - every operation's scope exists in `scope.yaml`
   - every scope used by an app user is in the `Consumer` role in `role.yaml`
   - every event name dispatched in `src/Service/*` is a key in `event.yaml`
   - every `setIncoming` / `setOutgoing` / `addThrow` class exists in `src/Model` (rerun `generate:model` if not)
   - every action class referenced in operations and cronjobs exists
3. Before you deploy, ask the user if `.env` / `FUSIO_URL` points to anything other than a local or dev instance.

## After deploying

- `php bin/fusio route` lists the routes that are now active.
- If the deploy reports an error, fix the referenced resource file and deploy again. Deploy is idempotent.
- To enable an optional section (`connection`, `plan`, `agent`), create `resources/<name>.yaml` and uncomment the
  matching line in `.fusio.yml`.

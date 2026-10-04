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

1. **Check the login state:** `php bin/fusio whoami`. The login token is stored in `fusio_token.json` in the project
   root. Read the result like this:
   - prints the user (YAML with `id`, `name`, `scopes`): logged in, continue with the deploy
   - `Found no existing token, please request a token through the login command`: never logged in, so log in
   - `Existing token is expired, ...`: the token expired (after 2 days by default, see `fusio_expire_token` in
     `configuration.php`), so log in again
   - `Invalid access token` (with a stack trace): the token file is stale, typically because the database was
     reset or reinstalled. Run `php bin/fusio logout` to remove it, then log in again
2. **If not logged in, make sure a user account exists.** Login only works with an existing account. The installation
   (`migrations:migrate`) seeds an internal `Administrator` user (`admin@localhost.com`) whose password isn't known, so
   it can't be used to log in. On a fresh installation, create an administrator first (role `1`, since deploy needs
   admin rights):

   ```
   php bin/fusio adduser -n --role=1 --username="<user>" --email="<email>" --password="<password>"
   ```

3. **Log in** with those credentials:

   ```
   php bin/fusio login -n --username="<user>" --password="<password>"
   ```

4. **Deploy:** `php bin/fusio deploy`.

Run `adduser` and `login` yourself with flags, as shown. Always pass `-n`: without it, a missing option (e.g.
`--email`) opens an interactive prompt that hangs. See "Credentials" in `/fusio-setup` for how to choose the
username, email, and password. A failed login (wrong password, HTTP 401 `invalid_client`) keeps the previous token.

If you don't know the credentials of an existing account, ask the user for them, or create a new admin with
`adduser`. If `deploy` fails with an auth or token error (e.g. "Existing token is expired"), run the `login` command
again and retry the deploy. On a shared or production instance, ask the user to run `php bin/fusio login` without
flags in their own terminal instead, so the password doesn't end up in the conversation.

## Before deploying

1. `php -l` the changed PHP files (operation configs and actions). A fatal error stops the deploy.
2. Check cross-references:
   - every operation's scope exists in `scope.yaml`
   - every scope used by an app user is in the `Consumer` role in `role.yaml`
   - `role.yaml` still contains the Fusio default scopes (Administrator: `authorization, backend, consumer,
     default`; Consumer: `authorization, consumer, default`). Deploy replaces role scopes, so a missing `backend`
     scope locks new admins out of the backend
   - every event name dispatched in `src/Service/*` is a key in `event.yaml`
   - every `setIncoming` / `setOutgoing` / `addThrow` class exists in `src/Model` (rerun `generate:model` if not)
   - every action class referenced in operations and cronjobs exists
3. Before you deploy, ask the user if `.env` / `FUSIO_URL` points to anything other than a local or dev instance.

## After deploying

- `php bin/fusio route` lists the routes that are now active.
- Smoke test the changed endpoints with `php bin/fusio serve` (see "Testing endpoints with `serve`" in `CLAUDE.md`).
  It needs no web server.
- If calls fail with a `TypeError` in a constructor (`Argument #N ... must be of type X, Y given`), the compiled DI
  container is stale. Delete `cache/container.php*`. `system:clear_cache` doesn't remove it.
- **New scopes need a new token.** Token scopes are fixed when the token is issued. If the deploy created new scopes
  (e.g. `[CREATED] scope todo`), existing tokens, including the CLI token from `login`, don't include them and
  calls fail with "Access to this operation is not in the scope of the provided token". The users already have the
  scope through their role, so just run the `login` command again. This always
  happens on a fresh setup, because login (step 3) happens before the first deploy creates the app scopes.
- If the deploy reports an error, fix the referenced resource file and deploy again. Deploy is idempotent.
- To enable an optional section (`connection`, `plan`, `agent`), create `resources/<name>.yaml` and uncomment the
  matching line in `.fusio.yml`.

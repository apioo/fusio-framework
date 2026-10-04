---
name: fusio-setup
description: First-time setup of this Fusio project. Configures database credentials in .env, adjusts resources/config.yaml for the app, installs dependencies and tables, creates an admin user, and runs the first deploy. Use when a developer starts a new project from this template.
---

# Set up a new Fusio project

Walk through the steps in order. Check the current state first (e.g. does `vendor/` exist, is `.env` filled in) and
skip anything that is already done.

## 1. Dependencies

```
composer install
```

Requires PHP >= 8.4.

## 2. Database connection: `.env`

Ask the user for the database credentials, then set `FUSIO_CONNECTION` to a Doctrine DBAL URL:

- MySQL/MariaDB: `pdo-mysql://user:password@localhost/dbname`
- PostgreSQL: `pdo-pgsql://user:password@localhost/dbname`
- SQLite (local experiments): `pdo-sqlite:///path/to/db.sqlite`

Also review `FUSIO_URL` (the public base URL of the API, used in mails and links), `FUSIO_MAILER`, and
`FUSIO_MAIL_SENDER`. Never print secrets from `.env` back to the user.

## 3. API metadata: `resources/config.yaml`

Ask what the app is about, then update `info_title`, `info_description` (CommonMark allowed), `info_contact_*`,
`info_license_*`, and optionally the registration and password-reset mail texts. These values appear in the
generated OpenAPI spec and SDKs.

## 4. Remove the demo (optional)

Ask whether to keep the `todo` example. It is useful as a reference, so suggest keeping it until the first own
resource exists. To remove it, delete `src/{Action/Todo,Service/Todo.php,View/Todo.php}`, `resources/operations/todo/`,
the `todo.*` entries in `operation.yaml`, the `todo_*` events, the cronjob, the `todo` scope and role entries, and the
`Todo*` definitions in `typeschema.json` (then rerun `generate:model`). Keep `Message` and `Collection`. Only remove
the migration and the `app_todo` table classes if the migration has never run on a shared database.

## 5. Install tables, create the admin, log in (user runs or approves)

These steps must run in this order, because each one depends on the previous one:

1. **Install tables.** Ask before running this against their database:

   ```
   php bin/fusio migrations:migrate --no-interaction
   ```

2. **Create an administrator account.** `login` only works with an account whose password the user knows. The
   installation only seeds an internal `Administrator` user (`admin@localhost.com`) with an unknown password. If the
   database was reset, a stale `fusio_token.json` from the old installation remains, so run `php bin/fusio logout`
   first. Then create the account non-interactively (see "Credentials" below):

   ```
   php bin/fusio adduser -n --role=1 --username="<user>" --email="<email>" --password="<password>"
   ```

3. **Log in the CLI** with the same credentials. This stores an access token in `fusio_token.json`, which expires
   after 2 days by default:

   ```
   php bin/fusio login -n --username="<user>" --password="<password>"
   ```

4. **Deploy:** `php bin/fusio deploy` (see `/fusio-deploy`).
5. **Log in again** with the same `login` command. The first deploy creates the app scopes (e.g. `todo`). The token
   from step 3 was issued before they existed, so calls to private operations would fail with "not in the scope of
   the provided token".

Confirm each login with `php bin/fusio whoami`. It prints the user (YAML) when logged in, and an error otherwise (see
`/fusio-deploy` for what each error means).

### Credentials

- Ask the user for a username and email. If they don't care, suggest `admin` / `admin@localhost.com`.
- For the password, ask whether to use their own or let you generate one with
  `php -r 'echo bin2hex(random_bytes(12));'` (24 characters). Fusio requires at least 8 characters. Show a generated
  password to the user once, so they can log in to the backend later, and don't write it into any file.
- `--email` is required. Without it, `adduser` stops at an interactive prompt, or fails with "User email must not be
  empty" when run with `-n`. Always pass `-n` so a missing option fails immediately instead of hanging.
- Passwords passed as flags end up in the conversation and in shell history. That is fine for a local dev instance.
  For a shared or production instance, ask the user to run `php bin/fusio adduser` and `php bin/fusio login` without
  flags in their own terminal instead. The interactive prompts don't work through Claude Code's `!` prefix.

## 6. Next steps

Point the user to `/fusio-resource` to build their first endpoint. Mention that the backend UI can be installed with
`php bin/fusio marketplace:install fusio`, and that the web server document root must be `public/`.

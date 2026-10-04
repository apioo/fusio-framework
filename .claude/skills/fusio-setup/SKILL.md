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

```
php bin/fusio migrations:migrate --no-interaction   # installs Fusio + app tables
php bin/fusio adduser                  # create an administrator (interactive)
php bin/fusio login                    # authenticate the CLI (interactive)
php bin/fusio deploy                   # apply resources/*
```

`adduser` and `login` prompt for input, so ask the user to run them with the `!` prefix (e.g.
`! php bin/fusio adduser`). Ask before running `migrate` against their database.

## 6. Next steps

Point the user to `/fusio-resource` to build their first endpoint. Mention that the backend UI can be installed with
`php bin/fusio marketplace:install fusio`, and that the web server document root must be `public/`.

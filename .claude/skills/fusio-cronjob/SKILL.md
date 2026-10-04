---
name: fusio-cronjob
description: Add a periodic background job (cronjob) to this Fusio project. Use when logic has to run on a schedule, e.g. reminders, cleanup, sync, or reports.
---

# Add a cronjob

Reference: `resources/cronjob.yaml` + `src/Action/Todo/Reminder.php` + `Service\Todo::reminder()`.

1. **Business logic** goes in a service method (e.g. `Service\Product::cleanup()`), not in the action.
2. **Action** `src/Action/<Entity>/<Job>.php`: a `readonly` class that implements `Fusio\Engine\ActionInterface`,
   calls the service, and returns a `Model\Message` with `success` and `message`.
3. **Register** the job in `resources/cronjob.yaml`:

   ```yaml
   ProductCleanup:
     cron: "0 3 * * *"   # standard 5-field cron expression: daily at 03:00
     action: "App\\Action\\Product\\Cleanup"
   ```

   The key is the cronjob name (PascalCase). Backslashes in `action` must be escaped in double-quoted YAML.
4. Run `php bin/fusio deploy`.

The cronjob doesn't receive an HTTP request or user context, so don't rely on `$context->getUser()`. On the server,
Fusio cronjobs run through `php bin/fusio system:cronjob_execute`. Tell the user this must be scheduled in the system
crontab (or is handled by the Docker image).

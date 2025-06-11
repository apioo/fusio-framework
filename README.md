
<p align="center">
    <a href="https://www.fusio-project.org/" target="_blank"><img src="https://www.fusio-project.org/img/fusio_64px.png"></a>
</p>

# Fusio Framework

This repository contains a starter project to use [Fusio](https://github.com/apioo/fusio) as a framework.
You can find more general information about Fusio at the [website](https://www.fusio-project.org/) or GitHub [repository](https://github.com/apioo/fusio).

## Installation

* Run `composer install` to install all required dependencies
* Enter the correct database credentials at the `.env` file
* Run the command `php bin/fusio migrate`
  * This command installs the Fusio and app tables at the provided database
* Run the command `php bin/fusio adduser`
  * This command adds a new administrator account
* Run the command `php bin/fusio login`
  * To authenticate with the account which you have created
* Run the command `php bin/fusio deploy`
  * This command reads the config files at the `resources/` folder and creates the fitting resources.

> Note this repository does not contain the Fusio backend app, since we develop the complete API via source files. If you
want to use the backend app you need to install it from the marketplace via: `php bin/fusio marketplace:install fusio`

## Architecture

### Folder resources/

* `operations`
  > Folder which contains operation configurations
* `config.yaml`
  > Contains the Fusio system config
* `container.php`
  > Contains the [Symfony DI](https://symfony.com/doc/current/components/dependency_injection.html) container configuration
* `events.yaml`
  > Contains a list of events which are triggered by the app. Users can then register HTTP callbacks to receives those events
* `operations.yaml`
  > Contains all available operations with a reference to an operation file inside the `operations/` folder
* `typeschema.json`
  > Contains the [TypeSchema](https://typeschema.org/) specification to generate the model classes under `src/Model`

### Folder src/

* `Action`
  > Contains all action classes which are used at the defined operations
* `Migrations`
  > Contains all migration files to setup the database structure (`php bin/fusio migration:generate`)
* `Model`
  > Contains the generated model classes (`php bin/fusio generate:model`)
* `Service`
  > Contains the service classes which handle the business logic of your API
* `Table`
  > Contains all table classes (`php bin/fusio generate:table`)
* `View`
  > Contains custom views to return the collection and entity response


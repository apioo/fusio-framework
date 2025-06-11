
<p align="center">
    <a href="https://www.fusio-project.org/" target="_blank"><img src="https://www.fusio-project.org/img/fusio_64px.png"></a>
</p>

# Fusio Framework

This repository contains a starter project to use [Fusio](https://github.com/apioo/fusio) as a framework.
You can find more general information about Fusio at the [website](https://www.fusio-project.org/) or GitHub [repository](https://github.com/apioo/fusio).

## About

Fusio is an API management tool where you can configure i.e. operations, actions and schemas
through the backend. Using Fusio as a Framework basically only means that you can place
all this configuration in config files and put it under version control so that you
can always start and run a fully configured Fusio instance. Fusio provides a `deploy` command
which reads all configuration files under `resources` and post them to the internal REST API
like you would also do through the backend panel. This repository contains all configuration files and a demo todo endpoint which shows
how you can build a simple endpoint.

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

## Folder

### resources/

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

### src/

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

## Docker

This repository contains a [Dockerfile](./Dockerfile) and [GitHub action](./.github/workflows/docker.yml) to
automatically build a Docker image on push. You can then run this image on any Docker
platform, or you can also take a look at [Plant](https://github.com/apioo/fusio-plant)
which helps to run Fusio images on a server.

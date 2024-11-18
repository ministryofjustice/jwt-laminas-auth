**This repository has been archived and is no longer actively maintained**

----

# Laminas Auth JWT module

This module provides authentication storage via JWT.

## Installation

    composer require ministryofjustice/jwt-laminas-auth

Copy `jwt_laminas_auth.global.php` to your autoload config directory and
change the settings as appropriate.

Enable the module `JwtLaminasAuth` in your application config.

## Development using docker

This assumes you have docker and docker-compose installed

Install files to local vendor directory

```shell script
docker-compose run composer
```

Run unit tests

```shell script
docker-compose build unit-test && docker-compose run unit-test
```

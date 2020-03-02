# Zend Auth JWT module

This module provides authentication storage via JWT 

## Installation

Composer require ministryofjustice/jwt-zend-auth

Copy jwtzendauth.global.php to your autoload config directory and 
change the settings as appropriate.

Enable the module JwtZendAuth in your application config

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

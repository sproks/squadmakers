# Squadmakers test
## Used technologies
- Php
- Framework Symfony 6 as Api REST
- Mysql 
- Open Api (Swagger)

## Requirements
- MySql
- Php 8
- Composer

## Deployment steps:
1. Clone the repository in an empty folder
2. Execute the command "composer install"
3. Change the Database connection in .env
4. Execute the command "php bin/console doctrine:migrations:migrate"

## Documentation Path
Can find the api docs in path /api/doc

## Testing
First of all you must prepare database for testing doing the following command:
"php bin/console --env=test doctrine:database:create"
And:
"php bin/console --env=test doctrine:schema:create"

You can run tests by executing this command line: php bin/phpunit

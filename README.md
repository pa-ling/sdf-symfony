sdf-symfony
=====

## Requirements
* php 5.6 (+pdo, +gd)
* mysql (recommended: +mysql Workbench)

## Installation
- Clone Repo</br>
  `git clone https://github.com/pa-ling/sdf-symfony.git`
- Install Composer</br>
  `composer install` 
- Create DB</br>
  `php bin/console doctrine:database:create` 
- Update DB Schema</br>
  `php bin/console doctrine:schema:update --force`
- Create super admin user</br>
  `php bin/console fos:user:create adminuser --super-admin`
- Create upload media folder & set permission to 755</br>
  `mkdir uploads && mkdir uploads/media && chmod 755 uploads/*`

## Running
- Start <br/>
    `php bin/console server:start`
- Stop <br/>
    `php bin/console server:stop`
- Run <br/>
    `php bin/console server:run`
- Open in browser</br>
  `http://localhost:8000`

## Tests
- run all tests in the Util directory <br/>
    `./vendor/bin/phpunit -c app/ tests/AppBundle/Util --debug`
- run tests for the Calculator class <br/>
    `./vendor/bin/phpunit -c app/ tests/AppBundle/Util/CalculatorTest.php --debug`
- run all tests for the entire Bundle <br/>
    `./vendor/bin/phpunit -c app/ tests/AppBundle/ --debug`
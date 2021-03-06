sdf-symfony
=====

## Requirements
- PHP >=5.3.9 (+pdo, +gd)
- MySQL (recommended: +mysql Workbench)
- Composer

## Pre Installation
- Create Database(Optional).
- Clone this Repo:</br>
  `git clone https://github.com/pa-ling/sdf-symfony.git`
- Configuration: <br>
  - Create file app/config/parameters.yml or copy from app/config/parameters.yml.dist and rename it.
  - Set all your local paremeters.
  
## Installation
- Install Packages:</br>
  `composer install`<br>
  If you have memory issue, use this command:<br>
  `composer install -dmemory_limit=1G`<br>
- Create DB:</br>
  `php bin/console doctrine:database:create` 
- Update DB Schema:</br>
  `php bin/console doctrine:schema:update --force`
- Create super admin user:</br>
  `php bin/console fos:user:create adminuser --super-admin`
- Create upload media folder & set permission to 755:</br>
  `mkdir uploads && mkdir web/uploads/media && chmod 755 web/uploads/*`

## Running
- Start:<br/>
    `php bin/console server:start`
- Stop:<br/>
    `php bin/console server:stop`
- Run:<br/>
    `php bin/console server:run`
- Open in browser:</br>
  `http://localhost:8000`

## Tests
- Run all tests in the Util directory:<br/>
    `./vendor/bin/phpunit -c app/ tests/AppBundle/Util --debug`
- Run tests for the Calculator class:<br/>
    `./vendor/bin/phpunit -c app/ tests/AppBundle/Util/CalculatorTest.php --debug`
- Run all tests for the entire Bundle:<br/>
    `./vendor/bin/phpunit -c app/ tests/AppBundle/ --debug`

## Extra
- Run after install bundle:<br/>
    `php bin/console cache:clear`
- Generate AppBundle/Entity:<br>
    `php bin/console doctrine:generate:entities AppBundle/Entity`
- Dump composer Autoload:<br>
    `composer dump-autoload`
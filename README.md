sdf-symfony
=====

## Requirements ## 
* php 5.6 (+pdo, +gd)
* mysql (recommended: +mysql Workbench)

## Installation ##
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

## Execution ##
- Run </br>
  `php bin/console server:run` 
- Open in browser</br>
  `http://localhost:8000`</br>
  `http://localhost:8000/login`</br>
  `http://localhost:8000/admin/dashboard`

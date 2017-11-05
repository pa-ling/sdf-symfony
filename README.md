sdf-symfony
=====

## Requirements ## 
* php 5.6 (+pdo, +gd)
* mysql (recommended: +mysql Workbench)

## Installation ##
git clone https://github.com/pa-ling/sdf-symfony.git
composer install  
php app/console doctrine:database:create  
php app/console doctrine:schema:update --force  
php app/console fos:user:create adminuser --super-admin  
php app/console fos:user:activate adminuser  

## Execution ##
php app/console server:run  
http://localhost:8000  
http://localhost:8000/login  
http://localhost:8000/admin/dashboard  

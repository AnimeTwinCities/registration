Anime Twin Cities, Inc. Registration System
========================

Prerequisites
------------------
You must have installed the following:
* git (command line)
    * https://git-scm.com/book/en/v2/Getting-Started-Installing-Git
* Mysql 
    * You could also use mariaDb if on Linux
    * Other Database engines may work, however this is untested
    * Make sure you have created an empty database for the project
    * Also make sure you have a user/password create with permissions for that database
    * When installing Mysql be sure to choose the Legacy Authentication Method
* Apache (nginx is untested, it may work or require additional configuration)
* PHP 7.1+
* yarn 
    * https://yarnpkg.com/lang/en/docs/install/
* composer 
    * https://getcomposer.org/doc/00-intro.md
* NodeJS
    * https://nodejs.org/en/download/
* Configure php, git, yarn and composer to be in your $PATH for command line

Development Install
---------------
1. Download the source code
     1. In your development direction. 
         * `git clone git@github.com:AnimeTwinCities/registration.git [<directory name>]`
         * That will checkout the source code for the given directory
     2. Enter the new directory
         * `cd registration`
         * If you provided a `[<directory name>]`, use that in place of registration
2. Install package dependencies
     1. Update php.ini file (it should be in the directory php was installed to)
         * Uncomment ';extension=gd2'
         * Uncomment ';extension=pdo_mysql'
     2. Start composer install
         * `composer install`
         * You will  be asked questions during the install
         * for example, make sure you have your database information ready
         * For your mail information you could just provide your gmail setting
     3. Start yarn install
         * `cd web`
         * `yarn install`
         * return to the root directory
             * `cd ..`
3. Create Database
     1. Use Doctrine to generate all tables
         * `php bin/console doctrine:schema:update --force`
     2. Create your first user (change user/email/password)
         * `php bin/console fos:user:create testuser test@example.com p@ssword --super-admin`
4. Insert Seed data for registration badges
     1. Run the sql file located at: app/config/seedData.sql


Run Symfony Server
------------------
php bin/console server:start

Final Configurations
------------------
Login to the admin console
* 127.0.0.1:8000/admin
     * The ip address / port may be different. If it is change it to /admin
* Create a new event year in the admin console. Mark it as active
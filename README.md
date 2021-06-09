# `FunnyZoo™` project

### What does it do?

This is simple app showing usage of the basic CRUD in Symfony 5.2

You can manage your little ZOO by adding, editing and deleting Caretakers, Cages and Animals.

Also in you free time you can chat with co-workers.

That's all. Have fun ;)

## Installation

### Installing app locally:

Application should be pulled from repo and installed locally. For the demo we will use Apache as HTTP server with dummy domain `http://zoo.symfony`. For DB, we'll use MySQL

 - Clone repo
 
    `git clone https://github.com/biesior/zoo-symfony.git`
 
 - Go to the pulled dir

    `cd zoo-symfony`

 - Update Composer's dependencies:

    `composer install`

 - Create MySQL database called `zoo_symfony`, preferably with `utf8mb4_unicode_ci` encoding using your favourite GUI

 - Configure MySQL credentials in `.env.local` file (do not commit it and/or don't override `.env`), i.e.:

   `DATABASE_URL="mysql://user:pass@127.0.0.1:3306/zoo_symfony?serverVersion=5.7"`

 - Perform DB migrations, it contains DB structure and some sample data

   `php bin/console doctrine:migrations:migrate`

 - Add dummy domain to your `hosts` file, i.e.:

   `127.0.0.1 zoo.symfony`

 - Add vhost to your Apache config (fix the path if required)

   ```apacheconf
   <VirtualHost zoo.symfony:80>
        ServerAdmin biesior@gmail.com
        ServerName zoo.symfony
        DocumentRoot "/www/symfony/zoo.symfony/public"
    
        ErrorLog "/var/log/apache2/zoo.symfony-error_log"
        CustomLog "/var/log/apache2/zoo.symfony-access_log" common

        <Directory "/www/symfony/zoo.symfony/public">
        	DirectoryIndex index.php
	         Options Indexes FollowSymLinks ExecCGI Includes
        	 AllowOverride All
        	 Require all granted
	      </Directory>

   </VirtualHost>
   ```
 - Restart Apache server and flush DNS caches according to your OS
 - Visit address in your browser: http://zoo.symfony
   
### Running Rocket.Chat server

I assume, that we run default, empty Rocket.Chat server with only one chanel (General) and one admin user. We'll use sample Docker instances from [official image](https://hub.docker.com/_/rocket-chat).

 - Let's run Mongo DB with defaults:

   ```bash
   docker run --name db -d mongo:4.0 --smallfiles --replSet rs0 --oplogSize 128
   docker exec -ti db mongo --eval "printjson(rs.initiate())"
   ```
   
 - For running chat server itself we'll modify port to use `3000` and also will add `CREATE_TOKENS_FOR_USERS=true` to enable creating the chat accounts from app *ad-hoc*.

   ```bash
   docker run --name rocketchat -p 3000:3000 --link db --env ROOT_URL=http://localhost --env MONGO_OPLOG_URL=mongodb://db:27017/local --env CREATE_TOKENS_FOR_USERS=true -d rocket.chat
   ```


Visit http://localhost:3000 in your browser, configure new server, and create some admin account.

**IMPORTANT!** Be patient, it may take several seconds to start the Rocket Chat server ;) If browser shows connection's problems, just wait, or check if Docker containers works.

When you'll create admin's account, you need to create also add a Personal Access Token for him. To do it, please go to `My Account` > `Personal Access Tokens` and add new one (skip 2-factor authentication for this demo). Please store it somewhere in safe place as it will be impossible to re-read it later.

Finally, edit `config/services.yaml` > `parameters.chat_api` to set proper access token, user ID, host and port.


With best wishes, Marcus :)
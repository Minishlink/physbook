# How to deploy?
According to [Collaborating.md](https://github.com/Minishlink/physbook/blob/master/doc/Collaborating.md), the origin/master branch must be ready for deployment. You should deploy origin/master as soon as a commit is made on origin/master.

## Compiling
You first have to compile the assets with your tools on your local machine. (installed following [Setup.md](https://github.com/Minishlink/physbook/blob/master/doc/Setup.md))

Note that these steps are not necessary if this release has not changed your assets (.css and .js) nor your routing used in javascript files (expose: true).

1. Make sure you are at HEAD of origin/master
  * git pull
2. Clean your cache
  * Delete app/cache
3. Clean your web assets
  * Delete web/css/* and web/js/*
4. Generate javascript routes
  * php app/console fos:js-routing:dump --env=prod
5. Generate your assets
  * php app/console assetic:dump --env=prod

## Uploading
You should transfer the modified files, using your favorite FTP client (eg. FileZilla), overwriting every files that is newer. Most common files and folders are:

* src/
* web/js
* web/css
* web/images
* app/config/security.yml
* app/config/config.yml
* composer.json
* composer.lock

Particularly, you *MUST NOT* upload these files or folders, without asking the lead maintainer:

* app/config/parameters.yml

In case you added a cron task, upload the ext/anacrontab file in /etc/cron.

## Updating
Every logic in Symfony controllers should be updated now, but you should update your dependencies and should clear your production cache.

1. Update your database schema
  * php app/console doctrine:schema:update --dump-sql --force
2. Clear your cache
  * php app/console cache:clear --env=prod
3. Update composer dependencies (Symfony and bundles)
  * php composer.phar install
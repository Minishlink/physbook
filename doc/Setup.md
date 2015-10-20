# How do I get set up? #
This guide will help you setup your development environment.

## Mandatory tools ##
Download and install [Node+NPM](https://nodejs.org/download/) and [Composer](https://getcomposer.org/download/).

Then:
* npm install
* npm install -g gulp
* npm install -g bower
* npm install -g less

## Recommended environment ##
If you already have an environment you are familiar with, keep it. You just have to make sure your app/config/parameters.yml is well configured.

### Git ###

Git is a distributed version control system. It allows to collaborate more easily. 
Download Git for [Windows](http://msysgit.github.io) or for [Mac](http://git-scm.com/download/mac).

On Windows, if asked, add Git to the Windows Command Prompt.

If asked, Git should "Checkout as-is, commit Unix style endings".

You should have access to git on your console.

On Windows, if you want Git to remember your password, download and execute [Windows Credential Store for Git](http://gitcredentialstore.codeplex.com/).

### PHP local server ###

Windows : [WampServer](http://www.wampserver.com/) (make sure your PATH environment variable is configured correctly)

Mac : [XAMPP](https://www.apachefriends.org/fr/download.html)

### Code editor ###
Either use [Brackets](http://brackets.io) (Windows/Mac/Linux)
recommended with extensions:

* Brackets Git
* PHP Code Quality Tools (check PSR-1 and PSR-2 in the options)
* QuickDocsPHP
* Quick Search
* Exclude File Tree (exclude vendor, cache, logs)
* Todo

Or [PhpStorm](https://www.jetbrains.com/phpstorm/) (free 1 year license for students) with these plugins:
* Symfony
* PHP Annotations
* SensioLabsInsight

## Configure the repository ##

* git clone https://github.com/Minishlink/physbook.git physbook
* put the content of the "Site" Google Drive folder in physbook/ (images/fonts/tools/confidential docs...)
* bower install
* php composer.phar install
  * When asked about parameters, leave the default ones. (press ENTER again and again for each parameter)
* php app/console doctrine:create:database
* php app/console doctrine:schema:update --force
* php app/console doctrine:fixtures:load -n
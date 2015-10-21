# Tests #
Get started by reading the Symfony [guide on tests](http://symfony.com/doc/current/book/testing.html).

## Writing tests ##

### Data fixtures ###

### Unit tests ###

### Functional tests ###

## Automatic testing ##
On each push and each pull request, the SaaS Travis will test the app.
GitHub will then report the result with a green tick (or a red cross).

## Manual testing ##
You have to create the test database first.
* `php app/console doctrine:database:create --env=test`
* `php app/console doctrine:schema:create --env=test`

Download [phpunit.phar](https://phpunit.de/).

### Using the CLI ###
* go to the "physbook" directory
* `php app/console doctrine:fixtures:load -n --env=test`
* `php path/to/phpunit.phar -c app/`

### Using PhpStorm ###
[(official guide)](https://www.jetbrains.com/phpstorm/help/enabling-phpunit-support.html)

You have to configure the integration first.

* In File -> Settings -> Languages & Frameworks -> PHP -> PHP Unit:
    * specify the path to phpunit.phar
    * specify the path to phpunit.xml.dist (which is in app/)
* In Run -> Edit Configurations...:
    * add new PHP Unit configuration
        * Name: 'All tests'
        * Scope: 'defined in the configuration file'
        * add a new 'before launch / external tools'
            * Program: 'php' (or path to php)
            * Name: 'Load fixtures'
            * Parameters: 'app/console doctrine:fixtures:load -n --env=test'
            * Working directory: the path to "physbook" directory

Then you simply click on the 'Run' icon with 'All tests' as configuration.

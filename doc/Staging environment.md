# The staging environment

## When should I use it?
You can use it to test the new features in an environment which is more close to the production environment than your development machine. Some debug features are disabled but you still has access to the web profiler in order to do some benchmark.

## How do I access it?
The test server is located [here](http://test.physbook.fr). You need some login infos. (contact @Minishlink)

## How to deploy on the test server?
On each commit on origin/master, [DeployBot](http://deploybot.com/) will automatically fetch the commit, compile the assets and transfer the changed files on the test server. Then, a Slack notification will prompt you to finish deployment by executing [this script](http://test.physbook.fr/deploy.php). The script updates composer dependancies and clears the cache. It also purges the test database and loads the data fixtures.

## How do I login?
Each user has special roles, see the details in the [User data fixture](https://github.com/Minishlink/physbook/blob/master/src/PJM/AppBundle/DataFixtures/ORM/LoadUserData.php). The password is always `test`.

## What are data fixtures?
To sum up, they are a way to populate the database with test entries. They are in the folder [src/PJM/AppBundle/DataFixtures/ORM](https://github.com/Minishlink/physbook/tree/master/src/PJM/AppBundle/DataFixtures/ORM).

It is recommended to create new data fixtures for each new entities.

Learn more [here](http://symfony.com/fr/doc/current/bundles/DoctrineFixturesBundle/index.html).


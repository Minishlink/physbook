# How to collaborate? #

## Setup your environment ##
Please read [Setup.md](https://github.com/Minishlink/physbook/blob/master/doc/Setup.md).

## Workflow ##
We work with the [GitHub Flow](https://guides.github.com/introduction/flow). Basically, you should never commit changes directly on origin/master. The branch origin/master should always be ready to be deployed on the production server. It should be *stable*.

### Schema ###
When working on a new feature or a bugfix, you should always follow this workflow:

1. Create a new branch with an appropriate name (see [Branch naming](#branch-naming))
  * Inform your collaborators on Slack
2. Work on this branch
  * When committing, refer to other people with @mention and to issues with #number.
3. Test your modifications
  * Tests should be made continuously during your development
4. Create a pull request
  * You can create the pull request as soon as you create the branch so that team collaborators can discuss about the feature/fix.
5. Wait for the review of the team members
  * They comment about the code/feature and test the changes
6. Your modifications will be merged either by you or one of the team members when reviewed.
7. Delete that branch

### Branch naming ###
If you want to implement a new feature, give a representative name to the branch (eg. "survey").

If you want to fix a bug, name the branch "fix-*" where * is the page/feature it is related to (eg. "fix-events").

### Deployment ###
See [Deploy.md](https://github.com/Minishlink/physbook/blob/master/doc/Deploy.md). Once deployed, you should set a new release tag.

Example: say the previous version was 1.2.1. If there are new features in the deployed modifications, name the release 1.3. If there are only bug fixes, name the release 1.2.2. Switching to 2.0 would require a complete refactoring of the code or major new features: you should discuss with your collaborators.

## Conventions ##

### PHP Coding Standards ###
Phy'sbook follows the PHP coding standards of [Symfony](http://symfony.com/doc/current/contributing/code/standards.html), particularly [PSR-2](http://php-fig.org/psr/psr-2/).

You should run the [PHP Coding Standards Fixer](http://cs.sensiolabs.org) before each commit :

* php php-cs-fixer.phar fix src/

### Rules on origin/master ###
You should not commit/merge on origin/master any commented code, incomplete feature or TODO/FUTURE/FIXME comments. However, you can do it in your branches.

## Interesting articles ##
* [GitHub Flow in the Browser](https://help.github.com/articles/github-flow-in-the-browser/): step-by-step of the workflow using GitHub
* [Markdown syntax](https://guides.github.com/features/mastering-markdown): used when commenting or reviewing code, or editing the documentation files like this one
* [Closing issues via commit messages](https://help.github.com/articles/closing-issues-via-commit-messages/)
* [Deleting unused branches](https://help.github.com/articles/deleting-unused-branches/)

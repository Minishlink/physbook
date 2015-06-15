# README #

## What is Phy'sbook? ##

Phy'sbook is a social extranet for Arts et Métiers ParisTech students.

## License ##

    Copyright (C) 2014-2015  Louis Lagrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
## Who do I talk to? ##

Louis Lagrange : <louis.lagrange@gadz.org>
    
## How do I get set up? ##

### Install and configure Git ###

Download Git for [Windows](http://msysgit.github.io/) or for [Mac](http://git-scm.com/download/mac).
On Windows, install with [this option](https://raw.githubusercontent.com/zaggino/brackets-git/master/screenshots/gitInstall.png) and "Checkout as-is, commit Unix style endings".

### Install and configure a PHP local server ###

Windows : [WampServer](http://www.wampserver.com/) (download 32 bits version !)
Mac : [XAMPP](https://www.apachefriends.org/fr/download.html)

(Windows) Configure PATH path to PHP.

### Install and configure Brackets ###
Use [Brackets](http://brackets.io) (Windows/Mac/Linux)
recommended with extensions :

* Brackets Git
* PHP Code Quality Tools (check PSR-1 and PSR-2 in the options)
* QuickDocsPHP
* Quick Search
* Exclude File Tree
* Todo

### Package managers ###

#### Composer ####
https://getcomposer.org/download/

#### npm (+ node.js)) ####
https://nodejs.org/download/

#### Bower ####
http://bower.io/#install-bower

* npm install -g bower

### Outils ###

#### Less ####
http://lesscss.org/

* npm install -g less

### Configure repository ###

* git clone url physbook
* mettre parameters.yml et parameters_dev.yml (modifier si nécessaire)
* mettre dossier "Site" du Drive (images, fonts, utilitaires..)
* bower install
* php composer.phar install
* php app/console doctrine:create:database
* php app/console doctrine:schema:update --force

#### Création des boquettes principales ####
Dans phpmyadmin, créer les boquettes avec slug pians, paniers, brags et cvis.

#### Create test users ####
* php app/console fos:user:create admin --super-admin
* php app/console fos:user:create user
* php app/console users:create:inbox
* php app/console users:create:compte

## Contribution guidelines ##

PHP coding standard is [PSR-2](http://php-fig.org/psr/psr-2/).
Especially : use LF end of line and use 4 spaces for tabs.

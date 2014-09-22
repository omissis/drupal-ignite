Drupal Ignite
=============

This project contains a Drupal 7 template project that can be used to quickly set up a new environment.

NOTICE
------

This software is in early development stage and could still change a lot, so don't get mad if it still has a few raw edges :)


Installation
------------

* Run ```composer install``` from the root of the project
* Run ```bin/robo setup``` and provide an installation folder (eg: **/var/www/acme/website**), a domain and a site name (eg: **AcmeSite**), optionally a drupal ignite custom template git url.
* go to the installation folder (eg: ```cd /var/www/acme/website```)
* review and fix the parameters in the **build.loc.properties**, **build.dev.properties** and **build.stage.properties** files;
* start your database (MySQL, for instance);
* let phing build the local environment by typing ```bin/phing loc-app -verbose```.

Requirements
------------
* A Bash-compatible shell
* PHP 5.3.3+
* PHP extensions: json, curl
* [Composer](https://getcomposer.org)

Roadmap
-------

* make the script able to handle site names containing any character
* improve the setup provisioning script to handle a full installation
* add ansible provisioning as a main option instead of the shell script
* update and add more dependencies, both on composer and drush make
* remove italian as default language for the install profile
* add debug and prod environment
* add support for javascript testing frameworks
* add support for copying vhost file into apache config directory
* add platform/configuration detection to better target copies
* updating hosts file automatically
* improve input handling in setup.sh
* setting up mysql database
* add apache vhost templates for all environments
* add nginx vhost templates

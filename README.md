Drupal Ignite
=============

This project contains a Drupal 7 template project that can be used to quickly set up a new environment.

NOTICE
------

This software is in early development stage and could still change a lot, so don't get mad if it still has a few raw edges :)



Installation
------------

* run the setup.sh script and provide an installation folder (eg: **/var/www/com.acme.www**), a domain(eg: **www.acme.com**) and a site name (eg: **AcmeSite**);
* go to the installation folder (eg: ```cd /var/www/com.acme.www```)
* review and fix the parameters in the build.loc.properties, build.dev.properties and build.stage.properties files;
* start your database (MySQL, for instance);
* let phing build the local environment by typing ```bin/phing loc-app -verbose```.


Contents
--------

### root folder

* a tailored .gitignore file;
* a simple apache vhost conf file;
* a drush make file containing some basic modules and libraries;
* a behat.yml.dist file, containing a Drupal-optimized set of Behat configuration;
* some phing.properties files, containing the variables belonging to each environment;
* a phing build.xml file, containing some targets to build the site in the local, dev and stage environments;
* a composer.json file, containing all the dependencies needed by behat and phing;
* a phpunit.xml.dist, containing the default config for running phpunit tests.

### bin/ folder

* a small bash build file that downloads composer and phings and runs the build.

### dumps/ folder

* placeholder to make sure the directories is here. it will hold drush backups.

### features/ folder

* a bootstrap/Drupal/Ignite/ folder containing two Behat Contexts carrying some goodies;
* a files/ folder containing two images to use as fixtures.
* an example scenario.

### profiles/ folder

* it contains a very basic Drupal install profile.

### reports/ folder

* placeholder to make sure the directories is here. it will hold test results and code analysis reports.

### a _sites_ folder

* a drush folder containing alias configuration;
* a modules folder containing basic subfolders layout for future modules and features;
* an empty themes folder.

### a _test_ folder

* a phpunit bootstrap file;
* a csv file iterator;
* a migrate helper to load csv data sources.


Requirements
------------
* A Bash-compatible shell
* PHP 5.3.3+
* PHP extensions: json, curl


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

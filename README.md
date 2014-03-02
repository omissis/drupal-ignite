Drupal Ignite Core
==================

This project contains a drupal 7 template project that can be used to quickly set up a new environment.


Installation
------------

* install composer (http://getcomposer.org/)
* run the setup.sh script and provide a vendor name (eg: acme), a site name (eg: www) and an installation folder (eg: /var/www/com.acme.www)
* configure the right parameters in the build.loc.properties, build.dev.properties and build.stage.properties files.
* start a sql database (MySQL, for instance)
* initialize dependencies using ```composer install```
* let phing build the local environment by typing ```bin/phing loc-app -verbose```


Contents
--------

* a simple bash provisioning script
* a drush alias file
* a simple apache vhost conf file
* a tailored .gitignore file
* a drush make file containing some basic modules and libraries
* a behat.yml.dist file, containing the template for the behat configuration
* a phing build.xml file, containing some targets to build the site in the local, dev and stage environments
* some phing.properties files, containing the variables belonging to each environment
* a composer.json file, containing all the dependencies needed by behat and phing
* a phpunit.xml, containing the config for running phpunit tests
* a _features_ directory, containing some behat files and a sample feature file with two scenarios
* a _sites_ directory, containing some basic directories laid out
* a _test_ directory, containing the phpunit's bootstrap file and some utility classes for testing


Requirements
------------
* A Bash-compatible shell
* PHP 5.3.3+
* PHP extensions: json, curl


Roadmap
-------

* improve the setup provisioning script to handle a full installation
* add ansible provisioning as a main option instead of the shell script
* update and add more dependencies, both on composer and drush make
* remove italian as default language
* add debug and prod environment
* add support for javascript testing frameworks
* add support for copying vhost file into apache config directory
* add platform/configuration detection to better target copies

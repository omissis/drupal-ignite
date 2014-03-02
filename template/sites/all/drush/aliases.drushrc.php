<?php
/**
 * @file
 * Define drush aliases for the current project.
 */

/**
 * Alias for local environment.
 *
 * Assumes <site-name>_loc/<site-name>_loc as username/password for the database
 * and admin/admin for the website administrator username/password. YOU SHOULD CHANGE THIS.
 */

$loc_db_url = 'mysql://__name___loc:__name___loc@127.0.0.1/__name___loc';

$aliases['__name__.loc'] = array(
  'uri' => 'loc.__domain__',
  'root' => '__docroot__',
  'db-url' => $loc_db_url,
  'path-aliases' => array(
    '%drush' => '__docroot__/vendor/drush/drush',
    '%drush-script' => '__docroot__/bin/drush',
    '%dump-dir' => '__docroot__/dumps',
    '%files' => 'sites/default/files',
  ),
  'target-command-specific' => array(
    'sql-sync' => array(
      'no-cache' => TRUE,
    ),
  ),
  'command-specific' => array(
    'site-install' => array(
      'site-name' => '__originalname__',
      'site-mail' => 'info@loc.__domain__',
      'db-url'    => $loc_db_url,
      'account-mail' => 'admin@loc.__domain__',
      'account-name' => 'admin',
      'account-pass' => 'admin',
    ),
  ),
);

/**
 * Alias for development environment.
 *
 * You should at least ensure 'root' path is correct.
 */
// $aliases['__name__.dev'] = array(
//   'uri' => 'dev.__domain__',
//   'root' => '/var/www/__domain__/dev',
//   'remote-host' => 'dev.__domain__',
//   'remote-user' => 'root',
//   'command-specific' => array(
//     'sql-sync' => array(
//       'no-cache' => TRUE,
//     ),
//   ),
// );

/**
 * Alias for staging environment.
 *
 * You should at least ensure 'root' path is correct.
 */
// $aliases['__name__.stage'] = array(
//   'uri' => 'stage.__domain__',
//   'root' => '/var/www/__domain__/stage',
//   'remote-host' => 'stage.__domain__',
//   'remote-user' => 'root',
//   'command-specific' => array(
//     'sql-sync' => array(
//       'no-cache' => TRUE,
//     ),
//   ),
// );

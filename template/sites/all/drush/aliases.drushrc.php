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

$loc_db_url = 'mysql://__site___loc:__site___loc@127.0.0.1/__vendor_____site___loc';

$aliases['__vendor__.__site__.loc'] = array(
  'uri' => 'loc.__site__.__vendor__.__tld__',
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
      'site-name' => ucfirst('__site__'),
      'site-mail' => 'info@loc.__site__.__vendor__.__tld__',
      'db-url'    => $loc_db_url,
      'account-mail' => 'admin@loc.__site__.__vendor__.__tld__',
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
// $aliases['__vendor__.__site__.dev'] = array(
//   'uri' => 'dev.__site__.__vendor__.__tld__',
//   'root' => '/var/www/__vendor__/__site__/dev',
//   'remote-host' => 'dev.__site__.__vendor__.__tld__',
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
// $aliases['__vendor__.__site__.stage'] = array(
//   'uri' => 'stage.__site__.__vendor__.__tld__',
//   'root' => '/var/www/__vendor__/__site__/stage',
//   'remote-host' => 'stage.__site__.__vendor__.__tld__',
//   'remote-user' => 'root',
//   'command-specific' => array(
//     'sql-sync' => array(
//       'no-cache' => TRUE,
//     ),
//   ),
// );

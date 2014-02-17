<?php
/**
 * @file
 * Define drush aliases for the current project.
 */

// Define home directory.
$home_directory = __DIR__ . '/../../../';

// Create aliases.
$aliases['__site__.loc'] = array(
  'uri' => 'loc.__site__.__tld__',
  'root' => $home_directory . '/Sites/__vendor__/__site__',
  'db-url' => 'mysql://__site___loc:__site___loc@localhost/__site___loc',
  'path-aliases' => array(
    '%drush' => $home_directory . '/pear/bin',
    '%drush-script' => $home_directory . '/pear/bin/drush',
    '%dump-dir' => $home_directory . '/Sites/__vendor__/__site__/dumps',
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
      'site-mail' => 'info@loc.__site__.__tld__',
      'db-url'    => 'mysql://__site___loc:__site___loc@127.0.0.1/__site__',
      'account-mail' => 'info@loc.__site__.__tld__',
      'account-name' => 'admin',
      'account-pass' => 'admin',
    ),
  ),
);

$aliases['__site__.dev'] = array(
  'uri' => 'dev.__site__.__vendor__.__tld__',
  'root' => '/var/www/dev.__site__.__vendor__.__tld__',
  'remote-host' => 'dev.__site__.__vendor__.__tld__',
  'remote-user' => 'root',
  'command-specific' => array(
    'sql-sync' => array(
      'no-cache' => TRUE,
    ),
  ),
);

$aliases['__site__.stage'] = array(
  'uri' => 'stage.__site__.__vendor__.__tld__',
  'root' => '/var/www/stage.__site__.__vendor__.__tld__',
  'remote-host' => 'stage.__site__.__vendor__.__tld__',
  'remote-user' => 'root',
  'command-specific' => array(
    'sql-sync' => array(
      'no-cache' => TRUE,
    ),
  ),
);

<?php

$_SERVER['HTTP_HOST']   = 'default';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('DRUPAL_ROOT', dirname(__DIR__));
set_include_path(DRUPAL_ROOT . PATH_SEPARATOR . get_include_path());
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

// Include all the other files in this directory
if ($handle = opendir('.')) {
    while (false !== ($entry = readdir($handle))) {
        if (!in_array($entry, array('.', '..', __FILE__), true)) {
            require_once $entry;
        }
    }
    closedir($handle);
}

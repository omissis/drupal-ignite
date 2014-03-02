<?php
/**
 * @file
 * Enables modules and site configuration for a minimal site installation.
 */

/**
 * Implements hook_install_tasks()
 */
function __name___install_tasks() {
  return array(
    '__name___configure_site_features' => array(
      'display_name' => st('Configure site features'),
    ),
  );
}

/**
 * Implements hook_install_tasks() callback
 */
function __name___configure_site_features() {
  // Revert features
  $features = features_get_features();
  foreach ($features as $name => $feature) {
    if ($feature->status) {
      features_revert(array($name => array('variable', 'user_permission')));
    }
  }
  cache_clear_all();
}

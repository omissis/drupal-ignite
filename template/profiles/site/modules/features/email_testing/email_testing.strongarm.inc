<?php
/**
 * @file
 * email_testing.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function email_testing_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'reroute_email_enable';
  $strongarm->value = 1;
  $export['reroute_email_enable'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'reroute_email_enable_message';
  $strongarm->value = 1;
  $export['reroute_email_enable_message'] = $strongarm;

  return $export;
}

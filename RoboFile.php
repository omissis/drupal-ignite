<?php

use Symfony\Component\Finder\Finder as Finder;
use Symfony\Component\Filesystem\Filesystem as Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class RoboFile extends \Robo\Tasks
{
  const STABLE_BRANCH = '0.1';

  /**
   * Setup drupal-ignite.
   */
    public function setup($name = false, $domain = false, $docroot = false) {
       $this->yell("Drupal-ignite setup");
       $this->say("DrupalIgnite RELEASE: " . self::STABLE_BRANCH);
       if (!$name) {
         $name = $this->ask("Please enter Site's Name:");
       }
       if (!$domain) {
         $domain = $this->ask("Please enter Site's Domain:");
       }
       if (!$docroot) {
         $docroot = $this->ask("Please enter Site's Root Folder:");
       }
       $this->createDocroot($docroot);
    }

    public function createDocroot($docroot) {
      $fs = new Filesystem();
      $finder = new Finder();
      $this->printTaskInfo('Creating document root...');
      if (!$fs->exists($docroot)) {
        try {
          $fs->mkdir($docroot);
        } catch (IOExceptionInterface $e) {
          $this->say("An error occurred while creating your directory at " . $e->getPath());
          $this->stopOnFail(true);
        }
      }
      else {
        if (count($finder->files()->in($docroot))) {
          $empty = false;
          while (!in_array($empty, array('y', 'n'))) {
            $empty = $this->ask("Folder '$docroot' already exists, should I empty it now? [y/n]");
            if ($empty == 'n') {
              $this->say('<error>Folder needs to be empty in order to continue.</error>');
              return FALSE;
            }
          }
        }
      }
    }
}

<?php

use Symfony\Component\Finder\Finder as Finder;
use Symfony\Component\Filesystem\Filesystem as Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class RoboFile extends \Robo\Tasks
{
  const VERSION = '0.1';

  /**
   * Setup drupal-ignite.
   */
  public function setup($name = false, $domain = false, $docroot = false) {
    $this->yell("Drupal-ignite setup");
    $this->say("DrupalIgnite RELEASE: " . self::VERSION);

    if (!$name) {
       $name = $this->ask("Please enter Site's Name:");
    }
    if (!$domain) {
      $domain = $this->ask("Please enter Site's Domain:");
    }
    if (!$docroot) {
      $docroot = $this->ask("Please enter Site's Root Folder:");
    }
    $temp_dir = sys_get_temp_dir() . "/" . uniqid('drupal-ignite');
    $this->createDocroot($docroot);
    $this->buildTemplate($name, $domain, $docroot, $temp_dir);
    $this->finalize($docroot, $temp_dir);
  }

  public function finalize($docroot, $temp_dir) {
    $this->printTaskInfo('Building project from template...');
    $this->taskMirrorDir([$temp_dir => $docroot])->run();
    $this->taskDeleteDir($temp_dir)->run();
    $this->say('Congratulations, all done');
    $this->yell('You can access your project by here: ' . $docroot);
  }

  public function buildTemplate($name, $domain, $docroot, $temp_dir) {
    $this->printTaskInfo('Building project from template...');
    $finder = new Finder();

    // Create temp folder.
    $safe_name = $this->slugify($name);

    // Static replacements.
    $tokens = array(
      '__originalname__' => $name,
      '__docroot__'      => $docroot,
      '__domain__'       => $domain,
      '__name__'         => $safe_name,
    );

    // Create temporary directory.
    $this->taskFileSystemStack()
         ->mkdir($temp_dir)
         ->run();

    // Mirror template.
    $this->taskMirrorDir(['template' => $temp_dir])->run();

    // Replace strings in file.
    $files = Finder::create()->ignoreVCS(true)
        ->files()
        ->in($temp_dir);
    foreach ($files as $file) {
      foreach ($tokens as $token => $replace) {
        $this->taskReplaceInFile($file->getRealpath())
         ->from($token)
         ->to($replace)
         ->run();
      }
    }

    // Rename directories.
    foreach (array('directories', 'files') as $traversal) {
      $files = Finder::create()->ignoreVCS(true)
          ->name('*__name__*')
          ->$traversal()
          ->in($temp_dir);
      $fs_stack = $this->taskFileSystemStack();
      foreach ($files as $file) {
        $from = $file->getRealpath();
        $to = str_replace('__name__', $safe_name, $from);
        $fs_stack->rename($from, $to);
      }
      $fs_stack->run();
    }
  }

  public function createDocroot($docroot) {
    $this->printTaskInfo('Creating document root...');
    $fs = new Filesystem();
    $fs_stack = $this->taskFileSystemStack();
    $finder = new Finder();
    if (!$fs->exists($docroot)) {
      $fs_stack->mkdir($docroot)->run();
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
          $this->taskCleanDir($docroot)->run();
        }
      }
    }
  }

  private function slugify($text) {
    $text = preg_replace('~[^\\pL\d]+~u', '_', $text);
    $text = trim($text, '_');
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text;
  }

}

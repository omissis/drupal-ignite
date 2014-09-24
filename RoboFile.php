<?php

use Symfony\Component\Finder\Finder as Finder;

class RoboFile extends \Robo\Tasks
{

  // Standard template url.
  private $standardUrlTemplate = 'git@github.com:paolomainardi/drupal-ignite-standard-template.git';

  /**
   * Setup drupal-ignite.
   */
  public function setup($name = false, $domain = false, $docroot = false, $opts = ['template' => 'false'])
  {
    $this->stopOnFail(true);
    $this->yell("Drupal-ignite setup");
    if (!$name) {
      $name = $this->ask("Please enter Site's Name:");
    }
    if (!$domain) {
      $domain = $this->ask("Please enter Site's Domain:");
    }
    if (!$docroot) {
      $docroot = $this->ask("Please enter Site's Root Folder:");
    }
    if ($opts['template'] !== 'false') {
      $this->standardUrlTemplate = $opts['template'];
    }
    $temp_dir = sys_get_temp_dir() . "/" . uniqid('drupal-ignite');
    $this->createDocroot($docroot);
    $this->buildTemplate($name, $domain, $docroot, $temp_dir);
    $this->finalize($docroot, $temp_dir);
  }

  public function buildTemplate($name, $domain, $docroot, $temp_dir)
  {
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

    // Clone template.
    $this->taskGitStack()
        ->cloneRepo($this->standardUrlTemplate, $temp_dir)
        ->run();

    // Download submodule if exists.
    if (file_exists("{$temp_dir}/.gitmodules")) {
      $this->taskExecStack()
        ->stopOnFail()
        ->exec("cd {$temp_dir} && git submodule update --init --recursive --remote")
        ->run();

      // Remove .gitmodules file.
      $this->taskFileSystemStack()
        ->remove("{$temp_dir}/.gitmodules")
        ->run();

      // Remove ".git" references from submodules.
      $files = Finder::create()
        ->files()
        ->in($temp_dir)
        ->ignoreVCS(false)
        ->ignoreDotFiles(false)
        ->name('.git');
      foreach ($files as $file) {
        $this->taskFileSystemStack()
          ->remove($file->getRealpath())
          ->run();
      }
    }

    $this->say('Removing git stuff...');
    $this->taskDeleteDir($temp_dir . '/.git')->run();

    // Replace strings in file.
    $files = Finder::create()
        ->ignoreVCS(true)
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

  public function createDocroot($docroot)
  {
    $this->printTaskInfo('Creating document root...');
    $fs_stack = $this->taskFileSystemStack();
    $finder = new Finder();
    if (!is_dir($docroot)) {
      $fs_stack->mkdir($docroot)->run();
    } else {
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

  public function finalize($docroot, $temp_dir)
  {
    $this->printTaskInfo('Building project from template...');

    // Mirror temporary dir and remove it.
    $this->taskMirrorDir([$temp_dir => $docroot])->run();
    $this->taskDeleteDir($temp_dir)->run();

    // Download and run composer.
    $this->taskExecStack()
     ->stopOnFail()
     ->exec("curl -sS https://getcomposer.org/installer | php -- --install-dir={$docroot}/bin")
     ->exec("cd {$docroot} && php {$docroot}/bin/composer.phar install --prefer-dist --verbose")
     ->run();

    $this->say('Congratulations, all done');
    $this->yell('Your drupal ignited project can be found here: ' . $docroot);
  }

  private function slugify($text)
  {
    $text = preg_replace('~[^\\pL\d]+~u', '_', $text);
    $text = trim($text, '_');
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);

    return $text;
  }
}

<?php

namespace Test;

class MigrateCsvFileIterator extends CsvFileIterator
{
    public function __construct($name, $moduleName)
    {
        if (function_exists('drupal_get_path')) {
            $path = DRUPAL_ROOT . drupal_get_path('module', $moduleName) . $name . '.csv';
        } else {
            $path = DRUPAL_ROOT . "/sites/all/modules/custom/$moduleName/data/$name.csv";
        }

        parent::__construct($path, 0, ';', '"', true);
    }
}

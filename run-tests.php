#!/usr/bin/env php
<?php 

// Environment init
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');
set_include_path(realpath(__DIR__ . '/../..') . PATH_SEPARATOR . get_include_path());

// Autoloaders
if (file_exists(__DIR__ . '/../Loader.php')) {
    require_once __DIR__ . '/../Loader.php';
}
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Translate function simulation
function __($txt) { return $txt; }

// Run
$dir = isset($_SERVER['argv'][1]) ? realpath($_SERVER['argv'][1]) : getcwd();
$filter = isset($_SERVER['argv'][2]) ? '/' . $_SERVER['argv'][2] . '/' : null;
Osf\Test\Runner::runDirectory($dir, '/Test', $filter) || exit(1);

#!/usr/bin/env php
<?php

// Environment init
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');
set_include_path(realpath(__DIR__ . '/../..') . PATH_SEPARATOR . get_include_path());

// Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else if (file_exists(__DIR__ . '/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
} else if (file_exists(__DIR__ . '/../Loader/autoload.php')) {
    require_once __DIR__ . '/../Loader/autoload.php';
}

// Translate function simulation
function __($txt) { return $txt; }

// Run
$dir = isset($_SERVER['argv'][1]) ? realpath($_SERVER['argv'][1]) : getcwd();
$filter = isset($_SERVER['argv'][2]) ? '/' . $_SERVER['argv'][2] . '/' : null;
\Osf\Test\Runner::runDirectory($dir, '/Test', $filter) || exit(1);
#!/usr/bin/env php
<?php 

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');

$zfPath = realpath(__DIR__ . '/../../../vendors/zendframework/library');
set_include_path(realpath('../..') . PATH_SEPARATOR . $zfPath . PATH_SEPARATOR . get_include_path());

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else if (file_exists(__DIR__ . '/../Loader.php')) {
    require_once __DIR__ . '/../Loader.php';
} else {
    trigger_error('No autoloading strategy found', E_USER_ERROR);
}

function __($txt) { return $txt; }

$dir = isset($_SERVER['argv'][1]) ? realpath($_SERVER['argv'][1]) : getcwd();
$filter = isset($_SERVER['argv'][2]) ? '/' . $_SERVER['argv'][2] . '/' : null;
Osf\Test\Runner::runDirectory($dir, '/Test', $filter) || exit(1);

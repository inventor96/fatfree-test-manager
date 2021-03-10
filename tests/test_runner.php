<?php

use inventor96\F3TestManager\TestManager;

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../src/TestManager.php');
require_once(__DIR__.'/../src/TestBase.php');

echo PHP_EOL."Running tests using separate calls.".PHP_EOL;
$test1 = new Test();
TestManager::runTests(__DIR__, $test1);
TestManager::reportTests($test1, false);

echo PHP_EOL."Running tests using one call.".PHP_EOL;
TestManager::runAndReportTests(__DIR__);

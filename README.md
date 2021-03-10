# Fat-Free Test Manager
A lightweight class to run and report the results from unit tests using the Fat-Free Framework Test class.

## Installation
```
composer require --dev inventor96/fatfree-test-manager
```

## Usage
The goal of this tool is to be as simple and lightweight as possible.

The idea is that we'll scan one folder (non-recursively) for all files that end with `Test.php` (e.g. `MyAwesomeTest.php`). For each of the files found, we find the first class definition, instantiate that class, and then call all public methods in that class that start with `test` (e.g. `public function testIfThisWorks() { ... }`). The class must extend `TestBase`.

With this structure in place, simply call `TestManager::runAndReportTests('directory/with/test/files');`.

If you have more than one folder with tests, you can create an instance of the [Fat-Free Framework `Test` class](https://fatfreeframework.com/3.7/test) and call `TestManager::runTests('a/directory/with/tests', $your_instance_of_Test);` for each directory, then call `TestManager::reportTests($your_instance_of_Test);` at the end.

By default, `runAndReportTests()` and `TestManager::reportTests()` will end the PHP process with an exit code of 1 if there were failed tests. To disable this behavior and allow the script to continue, set the last parameter to `false`.

## Example
`example_dir/ExampleTest.php`:
```php
<?php

use inventor96\F3TestManager\TestBase;

class ExampleTest extends TestBase {
	public function testPassExample() {
		$this->expect(true, 'the message for a passing test');
	}

	public function testFailExample() {
		$this->expect(false, 'the message for a failed test');
	}

	public function testPassAndFailExample() {
		$this->expect(true);
		$this->expect(false);
	}

	public function testWithException() {
		throw new Exception("This is the message for an exception");
	}
}
```

`example_dir/test_runner.php`:
```php
<?php

use inventor96\F3TestManager\TestManager;

require_once('../vendor/autoload.php');

echo PHP_EOL."Running tests using separate calls.".PHP_EOL;
$test1 = new Test();
TestManager::runTests(__DIR__, $test1);
TestManager::reportTests($test1, false);

echo PHP_EOL."Running tests using one call.".PHP_EOL;
TestManager::runAndReportTests(__DIR__);
```

Running the tests would look like this:
```
$ php test_runner.php 

Running tests using separate calls.
PASS: ExampleTest::testPassExample() // ExampleTest.php:7 - the message for a passing test
FAIL: ExampleTest::testFailExample() // ExampleTest.php:11 - the message for a failed test
PASS: ExampleTest::testPassAndFailExample() // ExampleTest.php:15
FAIL: ExampleTest::testPassAndFailExample() // ExampleTest.php:16
FAIL: ExampleTest::testWithException() // ExampleTest.php:20 - Exception: This is the message for an exception

Running tests using one call.
PASS: ExampleTest::testPassExample() // ExampleTest.php:7 - the message for a passing test
FAIL: ExampleTest::testFailExample() // ExampleTest.php:11 - the message for a failed test
PASS: ExampleTest::testPassAndFailExample() // ExampleTest.php:15
FAIL: ExampleTest::testPassAndFailExample() // ExampleTest.php:16
FAIL: ExampleTest::testWithException() // ExampleTest.php:20 - Exception: This is the message for an exception
```

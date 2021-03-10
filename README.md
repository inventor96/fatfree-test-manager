# Fat-Free Test Manager
A lightweight class to run and report the results from unit tests using the Fat-Free Framework Test class.

## Installation
```
composer require --dev inventor96/fatfree-test-manager
```

## Usage
The goal of this tool is to be as simple and lightweight as possible.

### Overview
The idea is that we'll scan one folder (non-recursively) for all files that end with `Test.php` (e.g. `MyAwesomeTest.php`). For each of the files found, we find the first class definition, instantiate that class, and then call all public methods in that class that start with `test` (e.g. `public function testIfThisWorks() { ... }`).

With this structure in place, simply call `TestManager::runAndReportTests('directory/with/test/files');`.

### Multiple Folders
If you have more than one folder with tests, you can create an instance of the [Fat-Free Framework `Test` class](https://fatfreeframework.com/3.7/test) and call `TestManager::runTests('a/directory/with/tests', $your_instance_of_Test);` for each directory, then call `TestManager::reportTests($your_instance_of_Test);` at the end.

### Exit Codes
By default, `runAndReportTests()` and `TestManager::reportTests()` will end the PHP process with an exit code of 1 if there were failed tests, or 0 if all were successful. To disable this behavior and allow the script to continue, set the last parameter to `false`.

### Extending `TestBase`
The test classes must extend `TestBase`, directly or indirectly. If indirectly (the test classes extend another class that extends `TestBase`), the constructor of the class(es) in the middle will want to pass an array as the second parameter to the parent constructor of class names that includes their own. This will allow the report to include the correct class and method name of the testing method.

For example:
```php
class MyTestBase extends TestBase {
	public function __construct(\Test &$test_instance) {
		parent::__construct($test_instance, [get_class()]);
	}
}
```

## General Example
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

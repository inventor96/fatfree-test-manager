# Fat-Free Test Manager
A lightweight class to run and report the results from unit tests using the [Fat-Free Framework `Test` class](https://fatfreeframework.com/3.7/test).

## Installation
```
composer require --dev inventor96/fatfree-test-manager
```

## Usage
The goal of this tool is to be as simple and lightweight as possible.

### Overview
The idea is that we'll scan one folder (non-recursively) for all files that end with `Test.php` (e.g. `MyAwesomeTest.php`). For each of the files found, we find the first class definition, instantiate that class, and then call all public methods in that class that start with `test` (e.g. `public function testIfThisWorks() { ... }`).

With this structure in place, simply call `TestManager::runAndReportTests('directory/with/test/files');`.

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

### Running code before/after test classes and methods
If a test class (or a class it extends) has a method named `preClass()`, `preTest()`, `postTest()`,  or `postClass()`; each method will be called at the respective time.

| Method | Called Time |
| ------ | ----------- |
| `preClass()` | Immediately after the class is instantiated |
| `preTest()` | Before each `test*()` method in the class |
| `postTest()` | After each `test*()` method in the class |
| `postClass()` | After all tests in the class have been run, and `postTest()` has been called (if present) |

### Multiple Folders
If you have more than one folder with tests, you can create an instance of the [Fat-Free Framework `Test` class](https://fatfreeframework.com/3.7/test) and call `TestManager::runTests('a/directory/with/tests', $your_instance_of_Test);` for each directory, then call `TestManager::reportTests($your_instance_of_Test);` at the end.

### Exit Codes
By default, `runAndReportTests()` and `reportTests()` will end the PHP process with an exit code of 1 if there were failed tests, or 0 if all were successful. To disable this behavior and allow the script to continue, set the last parameter to `false`.

## General Example
`example_dir/ExampleTest.php`:
```php
<?php

use inventor96\F3TestManager\TestBase;

class ExampleTest extends TestBase {
	private $pre_class_called = 0;
	private $post_class_called = 0;
	private $pre_test_called = 0;
	private $post_test_called = 0;

	public function preClass() {
		$this->pre_class_called++;
	}

	public function postClass() {
		$this->post_class_called++;

		echo "preClass() called: {$this->pre_class_called}".PHP_EOL;
		echo "postClass() called: {$this->post_class_called}".PHP_EOL;
		echo "preTest() called: {$this->pre_test_called}".PHP_EOL;
		echo "postTest() called: {$this->post_test_called}".PHP_EOL;
	}

	public function preTest() {
		$this->pre_test_called++;
	}

	public function postTest() {
		$this->post_test_called++;
	}

	private function testThisShouldNeverGetCalled() {
		// private methods should never get called by the TestManager
		$this->expect(false, 'This is not the method you are looking for...');
	}

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
preClass() called: 1
postClass() called: 1
preTest() called: 4
postTest() called: 4
PASS: ExampleTest::testPassExample() // ExampleTest.php:38 - the message for a passing test
FAIL: ExampleTest::testFailExample() // ExampleTest.php:42 - the message for a failed test
PASS: ExampleTest::testPassAndFailExample() // ExampleTest.php:46
FAIL: ExampleTest::testPassAndFailExample() // ExampleTest.php:47
FAIL: ExampleTest::testWithException() // ExampleTest.php:51 - Exception: This is the message for an exception

Running tests using one call.
preClass() called: 1
postClass() called: 1
preTest() called: 4
postTest() called: 4
PASS: ExampleTest::testPassExample() // ExampleTest.php:38 - the message for a passing test
FAIL: ExampleTest::testFailExample() // ExampleTest.php:42 - the message for a failed test
PASS: ExampleTest::testPassAndFailExample() // ExampleTest.php:46
FAIL: ExampleTest::testPassAndFailExample() // ExampleTest.php:47
FAIL: ExampleTest::testWithException() // ExampleTest.php:51 - Exception: This is the message for an exception
```

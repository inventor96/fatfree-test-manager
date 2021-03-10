<?php
namespace inventor96\F3TestManager;

use Test;

abstract class TestBase {
	/** @var array $me The names of classes that we don't want to use for displaying the tester method */
	protected $excluded_classes = [];

	/** @var Test $test The test instance */
	protected $test;

	/**
	 * Creates the basics for a unit test class
	 *
	 * @param \Test $test_instance The instance of the F3 `Test` class in which to store the tests
	 */
	public function __construct(Test &$test_instance, array $excluded_classes = []) {
		$this->test = $test_instance;
		$this->excluded_classes = array_merge([get_class()], $excluded_classes);
	}

	/**
	 * Evaluate a condition and save the result
	 *
	 * @param bool $condition A condition that evaluates to `true` or `false`
	 * @param string $message The message to attach to the test
	 * @return void
	 */
	protected function expect(bool $condition, string $message = ''): void {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($trace as $i => $t) {
			// don't want internal stuff
			if (in_array($t['class'], $this->excluded_classes, true)) {
				continue;
			}

			// get the class and method name of the testing method
			$final_message = $t['class'].'::'.$t['function'].'()';

			// add the file and line numer where this expect() was called
			if ($i > 0) {
				$prev = $trace[$i - 1];
				$final_message .= ' // '.basename($prev['file']).':'.$prev['line'];
			}

			// add a message, if present
			if ($message) {
				$final_message .= ' - '.$message;
			}

			break;
		}

		// store the test in the Test instance
		$this->test->expect($condition, $final_message);
	}
}

<?php
namespace inventor96\F3TestManager;

use \Exception;
use \Test;

class TestManager {
	/**
	 * Find all `*Test.php` files in the given directory, and run all `test*()` methods for the first class found in each file.
	 *
	 * @param string $directory The directory in which the `*Test.php` files are located
	 * @param Test $test_instance The instance of F3's `Test` class to run the tests with.
	 * @return void
	 */
	public static function runTests(string $directory, Test &$test_instance): void {
		$me = get_class();

		// get list of files
		$directory = rtrim($directory, '\\/');
		$files = glob($directory.DIRECTORY_SEPARATOR.'*Test.php');

		// make sure we actually have something to work with
		if ($files === false) {
			throw new Exception("There was an error while reading the {$directory} directory.");
		}

		// process each file
		foreach ($files as $file) {
			// get namespace and class from file
			$fp = fopen($file, 'r');
			$class = $namespace = $buffer = '';
			$i = 0;
			while (!$class) {
				if (feof($fp)) {
					break;
				}

				$buffer .= fread($fp, 512);
				$tokens = token_get_all($buffer);

				if (strpos($buffer, '{') === false) {
					continue;
				}

				for (; $i < count($tokens); $i++) {
					if ($tokens[$i][0] === T_NAMESPACE) {
						for ($j = $i + 1; $j < count($tokens); $j++) {
							if ($tokens[$j][0] === T_STRING) {
								$namespace .= '\\'.$tokens[$j][1];
							} else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
								break;
							}
						}
					}

					if ($tokens[$i][0] === T_CLASS) {
						for ($j = $i + 1; $j < count($tokens); $j++) {
							if ($tokens[$j] === '{') {
								$class = $tokens[$i + 2][1];
							}
						}
					}
				}
			}
			fclose($fp);

			// instantiate class
			require_once($file);
			$tester_class_name = "{$namespace}\\{$class}";
			$tester = new $tester_class_name($test_instance);

			// call each testing method
			$methods = get_class_methods($tester);
			foreach ($methods as $method) {
				// only call methods that start with 'test'
				if (strpos($method, 'test') !== 0) {
					continue;
				}
				
				// catch and report any errors that might happen
				try {
					$tester->{$method}();
				} catch (\Throwable $err) {
					$test_instance->expect(false, ltrim($tester_class_name, '\\').'::'.$method.'() // '.basename($err->getFile()).':'.$err->getLine().' - Exception: '.$err->getMessage());
				}
			}
		}
	}

	/**
	 * Echo the results of the tests run with the given instance of the F3 `Test` class.
	 *
	 * @param Test $test_instance The instance of the F3 `Test` class that contains the tests.
	 * @param bool $exit When set to true, it will end the PHP process, setting the exit code to 1 if there were failed tests.
	 * @return void
	 */
	public static function reportTests(Test $test_instance, bool $exit = true): void {
		// output results
		foreach ($test_instance->results() as $result) {
			echo ($result['status'] ? "\033[32mPASS\033[0m" : "\033[31mFAIL\033[0m").": {$result['text']}\n";
		}

		// exit
		if ($exit) {
			exit($test_instance->passed() ? 0 : 1);
		}
	}

	/**
	 * Find all `*Test.php` files in the given directory, and run all `test*()` methods for the first class found in each file.
	 * After the tests have finished, echo the results.
	 *
	 * @param string $directory The directory in which the `*Test.php` files are located
	 * @param Test $test_instance The instance of F3's `Test` class to run the tests with.
	 * @param bool $exit When set to true, it will end the PHP process, setting the exit code to 1 if there were failed tests.
	 * @return void
	 */
	public static function runAndReportTests(string $directory, Test &$test_instance = null, bool $exit = true): void {
		if ($test_instance === null) {
			$test_instance = new Test();
		}

		// run the tests
		self::runTests($directory, $test_instance);

		// output the report
		self::reportTests($test_instance, $exit);
	}
}

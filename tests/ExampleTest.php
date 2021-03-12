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

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

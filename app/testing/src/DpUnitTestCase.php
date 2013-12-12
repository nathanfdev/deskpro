<?php
class DpUnitTestCase extends \Codeception\TestCase\Test
{
	/**
	 * @var \CodeGuy
	 */
	protected $helper;

	protected function _before()
	{
		$this->helper = $this->codeGuy;
		$this->runBefore();
	}

	protected function _after()
	{
		$this->runAfter();
	}

	public function runBefore() {}
	public function runAfter() {}
}
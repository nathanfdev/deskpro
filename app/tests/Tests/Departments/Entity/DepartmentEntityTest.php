<?php

class DepartmentEntityTest extends DatabaseTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->getDb()->exec("DELETE FROM departments");

		DpTestConfig::resetContainer();
	}

	public function testSomething()
	{
		$this->markTestIncomplete();
	}
}
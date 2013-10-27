<?php

use Application\ApiBundle\Controller\AbstractController;
use Application\DeskPRO\Entity\Department;

// as AbstractController is abstract we have to create concrete implementation

class SomeController extends AbstractController
{

}

class ApiBundleControllerTest extends ControllerTestCase
{
	public function setUp()
	{
		parent::setUp();
	}

	public function testEntityValidatedUsingControllerIsEntityValidMethod()
	{
		/**
		 * @var SomeController $controller
		 */

		$controller = $this->createController('SomeController');

		$department = new Department();
		$department->setRealTitle('some title');
		$department->setUserTitle('some user title');

		$this->assertTrue($controller->isEntityValid($department));
	}

	public function testEntityNotValidatedUsingControllerIsEntityValidMethod()
	{
		/**
		 * @var SomeController $controller
		 */

		$controller = $this->createController('SomeController');

		$department = new Department();
		$department->setRealTitle('');
		$department->setUserTitle('some user title');

		$this->assertFalse($controller->isEntityValid($department));
	}
}
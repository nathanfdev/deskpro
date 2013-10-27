<?php

use Application\DeskPRO\Departments\TicketDepartmentEdit;
use Application\DeskPRO\Departments\Form\Type\TicketDepartmentType;

class TicketDepartmentEditTest extends DatabaseTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->getDb()->exec("DELETE FROM departments");

		$example_data = array(
			array('id' => 1, 'parent_id' => null, 'title' => 'Department 1', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 10),
				array('id' => 2, 'parent_id' => 1, 'title' => 'Department 1.1'),
			array('id' => 3, 'parent_id' => null, 'title' => 'Department 2'),
				array('id' => 4, 'parent_id' => 3, 'title' => 'Department 2.1'),
		);

		foreach ($example_data as $d) {
			$this->getDb()->insert('departments', $d);
		}

		DpTestConfig::resetContainer();
	}

	public function testFailingOfMoveDepartmentDueToTheReasonNewDepartmentMustNotBeAParentItself()
	{
		/**
		 * @var \Application\DeskPro\Entity\Department $department
		 */

		$em = $this->getEm();

		$department = $em->find('DeskPRO:Department', 2);

		$ticket_edit                  = new TicketDepartmentEdit($department);
		$ticket_edit->move_department = 'self';

		$department->parent = $em->find('DeskPRO:Department', 3);

		$errors = $this->validateObject($ticket_edit);

		$this->assertEquals(1, count($errors));
		$this->assertNotSame(false, strpos($errors[0]->getMessage(), 'New department must not be a parent itself'));
	}

	public function testFailingOfMoveDepartmentDueToNotSpecifyingWhereToMoveExistingTickets()
	{
		/**
		 * @var \Application\DeskPro\Entity\Department $department
		 */

		$em = $this->getEm();

		$department = $em->find('DeskPRO:Department', 1);

		$ticket_edit                  = new TicketDepartmentEdit($department);
		$ticket_edit->move_department = 'self';

		$department->parent = $em->find('DeskPRO:Department', 3);

		$errors = $this->validateObject($ticket_edit);

		$this->assertEquals(1, count($errors));
		$this->assertNotSame(false, strpos($errors[0]->getMessage(), 'Setting a new parent, must specify new department to move existing tickets to'));
	}

	public function testSuccessfulSavingOfDepartment()
	{
		/**
		 * @var \Application\DeskPro\Entity\Department $department
		 */

		$em = $this->getEm();

		$department = $em->find('DeskPRO:Department', 1);

		$ticket_edit = new TicketDepartmentEdit($department);

		$form = $this->createForm(new TicketDepartmentType(), $ticket_edit, array('cascade_validation' => true));

		$form->submit(
			array(
				 'department' => array(
					 'title'              => 'new title',
					 'user_title'         => 'new user title',
					 'is_tickets_enabled' => 0,
					 'is_chat_enabled'    => 1,
					 'display_order'      => 100
				 )
			)
		);

		$this->assertTrue($form->isValid());
		$this->assertEquals('new title', $department->getTitle());
		$this->assertEquals('new user title', $department->getUserTitle());

		$ticket_edit->save($this->getEm());

		$department = $em->find('DeskPRO:Department', $department->getId());

		$this->assertEquals('new title', $department->getTitle());
		$this->assertEquals('new user title', $department->getUserTitle());
	}

	public function testNotSavingOfDepartmentDueToValidationFailed()
	{
		// this is not working for now, refer to Tests/Departments/Form/Type/TicketDepartmentPropsType.php
	}
}
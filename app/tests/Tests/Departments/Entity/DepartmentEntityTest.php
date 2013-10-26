<?php

use Application\DeskPRO\Entity\Department;

class DepartmentEntityTest extends DatabaseTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->getDb()->exec("DELETE FROM departments");

		$example_data = array(
			array('id' => 1, 'parent_id' => null, 'title' => 'Department 1', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 10),
			array('id' => 2, 'parent_id' => null, 'title' => 'Department To Delete', 'is_tickets_enabled' => 0, 'is_chat_enabled' => 1, 'display_order' => 20),
		);

		foreach ($example_data as $d) {
			$this->getDb()->insert('departments', $d);
		}

		DpTestConfig::resetContainer();
	}

	public function testDepartmentCreate()
	{
		$em = $this->getEm();

		$department = new Department();
		$department->setRealTitle('some title');
		$department->setUserTitle('some user title');

		$em->persist($department);
		$em->flush();

		$department = $em->find('DeskPRO:Department', $department->id);

		$this->assertNotEmpty($department);
		$this->assertEquals('some title', $department->getRealTitle());
		$this->assertEquals('some user title', $department->getUserTitle());
	}

	public function testDepartmentUpdate()
	{
		$em = $this->getEm();

		$department = $em->find('DeskPRO:Department', 1);

		$department->setRealTitle('Department Updated');
		$department->setUserTitle('Department Updated User Title');

		$em->persist($department);
		$em->flush();

		$department = $em->find('DeskPRO:Department', 1);

		$this->assertNotEmpty($department);
		$this->assertEquals('Department Updated', $department->getRealTitle());
		$this->assertEquals('Department Updated User Title', $department->getUserTitle());
	}

	public function testDepartmentDelete()
	{
		$em = $this->getEm();

		$department = $em->find('DeskPRO:Department', 2);
		$em->remove($department);

		$em->flush();

		$department = $em->find('DeskPRO:Department', 2);

		$this->assertEmpty($department);
	}
}
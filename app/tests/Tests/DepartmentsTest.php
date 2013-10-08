<?php

class DepartmentsTest extends DatabaseTestCase
{
	/**
	 * @var \Application\DeskPRO\Departments\TicketDepartments
	 */
	private $ticket_deps;

	/**
	 * @var \Application\DeskPRO\Hierarchy\PreloadedHierarchy
	 */
	private $hierarchy;

	/**
	 * @var \Application\DeskPRO\Entity\Department[]
	 */
	private $deps;

	public function setUp()
	{
		parent::setUp();

		$this->getDb()->exec("DELETE FROM departments");

		$example_data = array(
			array('id' => 1, 'parent_id' => null, 'title' => 'Department A', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 10),
				// Sequential subs
				array('id' => 2, 'parent_id' => 1, 'title' => 'Sub A1', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 20),
				array('id' => 3, 'parent_id' => 1, 'title' => 'Sub A2', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 30),
			array('id' => 4, 'parent_id' => null, 'title' => 'Department B', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 40),
			array('id' => 5, 'parent_id' => null, 'title' => 'Department C', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 50),
			array('id' => 6, 'parent_id' => null, 'title' => 'Department D', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 60),
			array('id' => 7, 'parent_id' => null, 'title' => 'Department E', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 70),

			// Subs
			array('id' => 8, 'parent_id' => 5, 'title' => 'Sub C1', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 80),
			array('id' => 9, 'parent_id' => 5, 'title' => 'Sub C2', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 90),
			array('id' => 10, 'parent_id' => 7, 'title' => 'Sub E1', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 100),
			array('id' => 11, 'parent_id' => 7, 'title' => 'Sub E2', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 110),
			array('id' => 12, 'parent_id' => 7, 'title' => 'Sub E3', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 120),

			// Some chat departments to verify the repository
			array('id' => 13, 'parent_id' => null, 'title' => 'Chat 1', 'is_tickets_enabled' => 0, 'is_chat_enabled' => 1, 'display_order' => 130),
			array('id' => 14, 'parent_id' => null, 'title' => 'Chat 2', 'is_tickets_enabled' => 0, 'is_chat_enabled' => 1, 'display_order' => 140),
		);

		foreach ($example_data as $d) {
			$this->getDb()->insert('departments', $d);
		}

		$this->getDb()->replace('settings', array(
			'name' => 'core.tickets.default_department',
			'value' => '4'
		));

		DpTestConfig::resetContainer();

		$this->resetDepsObj();
	}

	public function resetDepsObj()
	{
		$this->ticket_deps = new \Application\DeskPRO\Departments\TicketDepartments($this->getEm());
		$this->deps        = \Orb\Util\Arrays::keyFromData($this->ticket_deps->getAll(), 'id');
	}

	public function testReset()
	{
		$all_before = $this->ticket_deps->getAll();
		$this->ticket_deps->reset();
		$all_after = $this->ticket_deps->getAll();

		$this->assertEquals($all_before, $all_after);
	}

	public function testTicketDeps()
	{
		$this->assertInstanceOf(
			'Application\DeskPRO\Departments\TicketDepartments',
			$this->ticket_deps
		);
	}

	public function testNoDefault()
	{
		$this->assertEquals(
			$this->ticket_deps->getDefaultDepartment(),
			$this->deps[2]
		);
	}

	public function testSetDefault()
	{
		$this->ticket_deps->setDefaultDepartmentPreference(8);
		$this->assertEquals(
			$this->ticket_deps->getDefaultDepartment(),
			$this->deps[8]
		);

		$this->ticket_deps->setDefaultDepartmentPreference($this->deps[8]);
		$this->assertEquals(
			$this->ticket_deps->getDefaultDepartment(),
			$this->deps[8]
		);

		$this->ticket_deps->setDefaultDepartmentPreference(null);
		$this->assertEquals(
			$this->ticket_deps->getDefaultDepartment(),
			$this->deps[2]
		);
	}

	public function testSetBadDefault()
	{
		$this->ticket_deps->setDefaultDepartmentPreference(100);
		$this->assertEquals(
			$this->ticket_deps->getDefaultDepartment(),
			$this->deps[2]
		);
	}

	public function testHierarchy()
	{
		$hierarchy = new \Application\DeskPRO\Hierarchy\PreloadedHierarchy($this->deps);

		foreach (array(
			'countRoots',
			'count',
			'getFlatArray',
			'getAll',
			'getAllIds',
			'getRootIds',
			'getRoots',
		) as $method) {
			$this->assertEquals(
				$hierarchy->$method(),
				$this->ticket_deps->$method(),
				"$method mismatch"
			);
		}

		foreach (array(1, 3) as $id) {
			foreach (array(
				'getChildren',
				'getChildrenIds',
				'countChildren',
				'hasChildren',
				'getParentPath',
				'getParentPathIds',
				'getParentId',
				'getParent',
				'isRoot',
				'isChild',
				'getById',
			) as $method) {
				$this->assertEquals(
					$hierarchy->$method($id),
					$this->ticket_deps->$method($id),
					"$method mismatch"
				);
			}
		}

		$this->assertEquals(
			$hierarchy->getByIds(array(1,2,3)),
			$this->ticket_deps->getByIds(array(1,2,3)),
			"getByIds mismatch"
		);
	}
}
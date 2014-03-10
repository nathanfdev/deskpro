<?php

namespace DpIntegrationTests\DeskPRO\Chat\Departments;

use Application\DeskPRO\Departments\ChatDepartmentEditor;

class ChatDepartmentsDataTest extends \DpIntegrationTestCase
{
	/**
	 * @var \Application\DeskPRO\Departments\ChatDepartments
	 */

	private $chat_departments;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDb');
		$this->helper->loadFixtures('General/ChatDepartmentsWithPermissionsData');

		$this->chat_departments = $this->helper->getSymfonyContainer()->getSystemService('chat_departments');
	}

	public function testGetChatDepartmentsEntities()
	{
		$this->assertNull($this->chat_departments->getById(100));
		$this->assertNotNull($this->chat_departments->getById(1));

		$this->assertEquals('Chat Department 1', $this->chat_departments->getById(1)->getTitle());

		$this->assertEquals(4, $this->chat_departments->count());
	}

	public function testUpdatingOfChatDepartmentsDisplayOrders()
	{
		$editor = new ChatDepartmentEditor($this->helper->getSymfonyContainer()->getEm());

		$editor->updateDisplayOrders(
			array(3, 4, 1, 2)
		);

		$this->assertEquals(30, $this->chat_departments->getById(1)->display_order);
		$this->assertEquals(40, $this->chat_departments->getById(2)->display_order);
		$this->assertEquals(10, $this->chat_departments->getById(3)->display_order);
		$this->assertEquals(20, $this->chat_departments->getById(4)->display_order);
	}

	public function testChildrenAndParentHierarchyForChatDepartments()
	{
		$dep1 = $this->chat_departments->getById(1);
		$dep2 = $this->chat_departments->getById(2);
		$dep3 = $this->chat_departments->getById(3);

		$this->assertEquals($dep1, $this->chat_departments->getParent($dep2));
		$this->assertEquals($dep1, $this->chat_departments->getParent(2));
		$this->assertEquals($dep1, $this->chat_departments->getParent($dep3));
		$this->assertEquals($dep1, $this->chat_departments->getParent(3));

		$this->assertEquals(2, sizeof($this->chat_departments->getChildren($dep1)));
		$this->assertEquals(2, sizeof($this->chat_departments->getChildren(1)));

		$this->assertEquals(2, sizeof($this->chat_departments->getRoots()));
	}

	public function testChatDepartmentsPermissionsInfo()
	{
		$permissions = $this->chat_departments->getPermissionsInfo($this->chat_departments->getById(1));

		$this->assertEquals(1, $permissions['usergroups'][0]['usergroup_id']);
		$this->assertEquals('full', $permissions['usergroups'][0]['perm_name']);

		$this->assertEquals(2, $permissions['usergroups'][1]['usergroup_id']);
		$this->assertEquals('full', $permissions['usergroups'][1]['perm_name']);

		$this->assertEquals(1, $permissions['agents'][0]['agent_id']);
		$this->assertEquals('full', $permissions['agents'][0]['perm_name']);

		$this->assertEquals(2, $permissions['agents'][1]['agent_id']);
		$this->assertEquals('full', $permissions['agents'][1]['perm_name']);
	}
}
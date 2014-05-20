<?php
namespace DpIntegrationTests\DeskPRO\Chat\Departments;

use Application\DeskPRO\Departments\ChatDepartmentEdit;
use \Application\DeskPRO\Departments\Form\Type\ChatDepartmentType;

class ChatDepartmentTypeTest extends \DpIntegrationTestCase
{
	/**
	 * @var \Symfony\Component\Form\Form
	 */

	private $form;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 */

	private $chat_departments;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDb');
		$this->helper->loadFixtures('General/ChatDepartmentsWithPermissionsData');

		$this->chat_departments = $this->helper->getSymfonyContainer()->getSystemService('chat_departments')->getById(1);
		$chat_department_edit   = new ChatDepartmentEdit($this->chat_departments);
		$this->form             = $this->helper->getSymfonyContainer()->getFormFactory()->create(new ChatDepartmentType(), $chat_department_edit);
	}

	public function testSuccessfulValidationOfChatDepartmentForm()
	{
		$this->assertFalse($this->form->isValid());

		$this->form->submit(array('department' => array('title' => 'test title')));

		$this->assertTrue($this->form->isValid());
		$this->assertTrue($this->form->isSynchronized());
	}

	public function testUnsuccessfulValidationOfChatDepartmentForm()
	{
		$this->assertFalse($this->form->isValid());

		$this->form->submit(array('department' => array('title' => '')));

		$this->assertFalse($this->form->isValid());
	}
}
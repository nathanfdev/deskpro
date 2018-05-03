<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\DepartmentHelper;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class DepartmentHelperTest.
 */
class DepartmentHelperTest extends AbstractWriterTest
{
    /**
     * @var DepartmentHelper
     */
    private $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->helper = $this->getContainer()->get('dp.importer.writer.helper.department');
        $this->clearTable('departments');

        parent::setUp();
    }

    public function test_create_ticket_department()
    {
        $department = $this->helper->findOrCreateDepartment('ticket', 'my department');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertNotNull($department->getId());
        $this->assertEquals('my department', $department->getTitle());
        $this->assertTrue($department->isTicketsEnabled());
        $this->assertFalse($department->isChatEnabled());
    }

    public function test_create_chat_department()
    {
        $department = $this->helper->findOrCreateDepartment('chat', 'my department');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertNotNull($department->getId());
        $this->assertEquals('my department', $department->getTitle());
        $this->assertFalse($department->isTicketsEnabled());
        $this->assertTrue($department->isChatEnabled());
    }

    public function test_get_existing_ticket_department()
    {
        $department = Department::createTicketDepartment();
        $department->setRealTitle('my department');

        $this->em()->persist($department);
        $this->em()->flush();
        $this->em()->clear();

        $oldId = $department->getId();

        $department = $this->helper->findOrCreateDepartment('ticket', 'my department');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals($oldId, $department->getId());
        $this->assertEquals('my department', $department->getTitle());
        $this->assertTrue($department->isTicketsEnabled());
        $this->assertFalse($department->isChatEnabled());
    }

    public function test_get_existing_chat_department()
    {
        $department = Department::createChatDepartment();
        $department->setRealTitle('my department');

        $this->em()->persist($department);
        $this->em()->flush();
        $this->em()->clear();

        $oldId = $department->getId();

        $department = $this->helper->findOrCreateDepartment('chat', 'my department');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals($oldId, $department->getId());
        $this->assertEquals('my department', $department->getTitle());
        $this->assertFalse($department->isTicketsEnabled());
        $this->assertTrue($department->isChatEnabled());
    }

    public function test_create_chat_department_if_the_same_ticket_department_title_exists()
    {
        $department = Department::createTicketDepartment();
        $department->setRealTitle('my department');

        $this->em()->persist($department);
        $this->em()->flush();
        $this->em()->clear();

        $oldId = $department->getId();

        $department = $this->helper->findOrCreateDepartment('chat', 'my department');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertNotEquals($oldId, $department->getId());
        $this->assertEquals('my department', $department->getTitle());
        $this->assertFalse($department->isTicketsEnabled());
        $this->assertTrue($department->isChatEnabled());
    }

    public function test_create_department_hierarchy()
    {
        $department = $this->helper->findOrCreateDepartment('ticket', 'my parent department > my child department');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('my child department', $department->getTitle());
        $this->assertTrue($department->isTicketsEnabled());
        $this->assertFalse($department->isChatEnabled());
        $this->assertNotNull($department->getParent());
        $this->assertEquals('my parent department', $department->getParent());
        $this->assertTrue($department->getParent()->isTicketsEnabled());
        $this->assertFalse($department->getParent()->isChatEnabled());
    }

    public function test_max_deep_level()
    {
        $department = $this->helper->findOrCreateDepartment('ticket', 'my parent department > my sub department 1 > my sub department 2');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('my sub department 1 > my sub department 2', $department->getTitle());
        $this->assertTrue($department->isTicketsEnabled());
        $this->assertFalse($department->isChatEnabled());
        $this->assertNotNull($department->getParent());
        $this->assertEquals('my parent department', $department->getParent());
        $this->assertTrue($department->getParent()->isTicketsEnabled());
        $this->assertFalse($department->getParent()->isChatEnabled());
    }

    public function test_parent_department_exists()
    {
        $department = Department::createTicketDepartment();
        $department->setRealTitle('my parent department');

        $this->em()->persist($department);
        $this->em()->flush();
        $this->em()->clear();

        $oldId = $department->getId();

        $department = $this->helper->findOrCreateDepartment('ticket', 'my parent department > my child department');
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('my child department', $department->getTitle());
        $this->assertTrue($department->isTicketsEnabled());
        $this->assertFalse($department->isChatEnabled());
        $this->assertNotNull($department->getParent());
        $this->assertEquals('my parent department', $department->getParent());
        $this->assertTrue($department->getParent()->isTicketsEnabled());
        $this->assertFalse($department->getParent()->isChatEnabled());
        $this->assertEquals($oldId, $department->getParent()->getId());
    }
}

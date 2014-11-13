<?php

namespace DpIntegrationTests\DeskPRO\Tickets\Departments;

class DepartmentDataTest extends \DpIntegrationTestCase
{
    public function runBefore()
    {
        $this->helper->enableDatabaseSet('EmptyDb');
        $this->helper->loadFixtures('General/SimpleDepartmentData');
    }

    public function testTicketDepartmentService()
    {
        /** @var \Application\DeskPRO\Departments\TicketDepartments $ticket_deps */
        $ticket_deps = $this->helper->getSymfonyContainer()->getSystemService('ticket_departments');
        $this->assertInstanceOf('Application\DeskPRO\Departments\TicketDepartments', $ticket_deps);

        $this->assertEquals($ticket_deps->count(), 4);
        $this->assertEquals(count($ticket_deps->getRoots()), 2);
        $this->assertTrue($ticket_deps->hasChildren(1), 'Department 1 should have children');
        $this->assertInstanceOf('Application\DeskPRO\Entity\Department', $ticket_deps->getParent(2), 'Department 1a should have a parent');

        $this->assertNull($ticket_deps->getById(100));
        $this->assertNotNull($ticket_deps->getById(1));
    }
}

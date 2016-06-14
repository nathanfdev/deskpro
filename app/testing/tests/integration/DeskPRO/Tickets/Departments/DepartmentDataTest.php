<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

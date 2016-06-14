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

namespace DpIntegrationTests\DeskPRO\ApiResult\Tickets;

use DpIntegrationTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__.'/../AbstractApiResultTest.php';

class GetTicketWithoutPermissionTest extends AbstractApiResultTest
{
    public function testFindByIdWithoutPermission()
    {
        $expectedTicketArray = $this->_getExpectedTicket();

        $ticketId = 1;

        $result = $this->getApiWithLimitedAccess()->tickets->findById($ticketId);

        $this->assertEquals('403', $result->getResponseCode());
    }

    public function testFindBySubjectWithoutPermission()
    {
        $testSubject = 'Test';

        $criteria = $this->getApiWithLimitedAccess()->tickets->createCriteria();

        $this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

        $criteria->addSubject($testSubject);

        $result = $this->getApiWithLimitedAccess()->tickets->find($criteria);

        $this->assertEquals('200', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('tickets', $data);

        $this->assertEquals(count($data['tickets']), 0);
    }

    /* TODO Fatal error: Call to undefined method DeskPRO\Criteria\Ticket::addDepartment() in /deskpro/www/app/testing/tests/integration/DeskPRO/ApiResult/Tickets/GetTicketWithoutPermissionTest.php on line 52
    public function testFindByDepartmentWithoutPermission()
    {
        $testDepartmentId = 1;

        $criteria = $this->getApiWithLimitedAccess()->tickets->createCriteria();

        $this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

        $criteria->addDepartment($testDepartmentId);

        $result = $this->getApiWithLimitedAccess()->tickets->find($criteria);

        $this->assertEquals('200', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('tickets', $data);

        $this->assertEquals(count($data['tickets']), 0);
    }
    */

    public function testFindByAgentWithoutPermission()
    {
        $testAgentId = 1;

        $testTicketId = 1;

        $criteria = $this->getApiWithLimitedAccess()->tickets->createCriteria();

        $this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

        $criteria->addAgent($testAgentId);

        $result = $this->getApiWithLimitedAccess()->tickets->find($criteria);

        $this->assertEquals('200', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('tickets', $data);

        $this->assertEquals(count($data['tickets']), 0);
    }

    public function testFindByCategoryWithoutPermission()
    {
        $testCategoryId = 1;

        $testTicketId = 1;

        $criteria = $this->getApiWithLimitedAccess()->tickets->createCriteria();

        $this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

        $criteria->addCategory($testCategoryId);

        $result = $this->getApiWithLimitedAccess()->tickets->find($criteria);

        $this->assertEquals('200', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('tickets', $data);

        $this->assertEquals(count($data['tickets']), 0);
    }

    public function testFindByOrganizationWithoutPermission()
    {
        $testOrganizationId = 1;

        $criteria = $this->getApiWithLimitedAccess()->tickets->createCriteria();

        $this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

        $criteria->addOrganization($testOrganizationId);

        $result = $this->getApiWithLimitedAccess()->tickets->find($criteria);

        $this->assertEquals('200', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('tickets', $data);

        $this->assertEquals(count($data['tickets']), 0);
    }
}

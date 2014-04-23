<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class GetTicketWithoutPermissionTest extends AbstractApiResultTest
{

	public function testFindByIdWithoutPermission()
	{
		$expectedTicketArray = $this->_getExpectedTicket();

		$ticketId = 1;

		$result = $this->getApiWithLimitedAccess()->tickets->findById($ticketId);
		
		$this->assertEquals('404', $result->getResponseCode());
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
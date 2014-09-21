<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class GetTicketTest extends AbstractApiResultTest
{
	public function testFindById()
	{
		$expectedTicketArray = $this->_getExpectedTicket();

		$ticketId = 1;

		$result = $this->getApi()->tickets->findById($ticketId);

		$this->assertInstanceOf('DeskPRO\Api\Result', $result);

		$this->assertArrayHasKey('ticket', $result->getData());

		$data = $result->getData();

		$retrievedTicketArray = $data['ticket'];

		foreach ($this->getDateTimeFields('ticket') as $field) {
			$this->assertIsValidDateTime($retrievedTicketArray[$field]);
		}

		foreach ($this->getDateTimeFields('person') as $field) {
			$this->assertIsValidDateTime($retrievedTicketArray['person'][$field]);
		}

		foreach ($this->getDateTimeFields('person_email') as $field) {
			$this->assertIsValidDateTime($retrievedTicketArray['person_email'][$field]);
		}

		foreach ($this->getTimestampFields('ticket') as $field) {
			// 0 check because some times can be null
			if ($retrievedTicketArray[$field] !== 0) {
				$this->assertIsValidTimestamp($retrievedTicketArray[$field], "ticket.$field");
			}
		}

		foreach ($this->getTimestampFields('person') as $field) {
			$this->assertIsValidTimestamp($retrievedTicketArray['person'][$field], "person.$field");
		}

		foreach ($this->getTimestampFields('person_email') as $field) {
			$this->assertIsValidTimestamp($retrievedTicketArray['person_email'][$field]);
		}

		foreach($this->_getIgnoreKeys('person') as $key) {
			$this->assertArrayHasKey($key, $retrievedTicketArray['person']);
			unset($retrievedTicketArray['person'][$key]);
			unset($expectedTicketArray['person'][$key]);
		}

		foreach($this->_getIgnoreKeys('person_email') as $key) {
			$this->assertArrayHasKey($key, $retrievedTicketArray['person_email']);
			unset($retrievedTicketArray['person_email'][$key]);
			unset($expectedTicketArray['person_email'][$key]);
		}

		foreach ($this->_getIgnoreKeys('ticket') as $key) {
			$this->assertArrayHasKey($key, $retrievedTicketArray);
			unset($retrievedTicketArray[$key]);
			unset($expectedTicketArray[$key]);
		}

		$this->assertEquals($retrievedTicketArray, $expectedTicketArray);
	}

	public function testFindBySubject()
	{
		$testSubject = 'Test';

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addSubject($testSubject);

		$result = $this->getApi()->tickets->find($criteria);

		$this->assertArrayHasKey('tickets', $result->getData());

		$data = $result->getData();

		$retrievedTicketArray = $data['tickets'][1];

		$this->assertEquals($retrievedTicketArray['subject'], $testSubject);
	}

	/* TODO Fatal error: Call to undefined method DeskPRO\Criteria\Ticket::addDepartment() in /deskpro/www/app/testing/tests/integration/DeskPRO/ApiResult/Tickets/GetTicketTest.php on line 101
	public function testFindByDepartment()
	{
		$testDepartmentId = 1;

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addDepartment($testDepartmentId);

		$result = $this->getApi()->tickets->find($criteria);

		$this->assertArrayHasKey('tickets', $result->getData());

		$data = $result->getData();

		$retrievedTicketArray = $data['tickets'][1];

		$this->assertEquals($retrievedTicketArray['department']['id'], $testDepartmentId);
	}
	*/

	public function testFindByAgent()
	{
		$testAgentId = 1;

		$testTicketId = 1;

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addAgent($testAgentId);

		$result = $this->getApi()->tickets->find($criteria);

		$data = $result->getData();

		$this->assertArrayHasKey('tickets', $data);

		$matchingTickets = $data['tickets'];

		$this->assertEquals(0, count($matchingTickets));

		$ticketBuilder = $this->getApi()->tickets->createBuilder();

		$this->assertInstanceOf('DeskPRO\Builder\Ticket', $ticketBuilder);

		$ticketBuilder->setId($testTicketId)->assignToAgent($testAgentId);

		$result = $this->getApi()->tickets->save($ticketBuilder);

		$this->assertFalse(!$result->getData());

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addAgent($testAgentId);

		$result = $this->getApi()->tickets->find($criteria);

		$data = $result->getData();

		$this->assertArrayHasKey('tickets', $data);

		$matchingTickets = $data['tickets'];

		$this->assertGreaterThanOrEqual(1, count($matchingTickets));

		$ticketBuilder->setId($testTicketId)->assignToAgent(0);

		$this->getApi()->tickets->save($ticketBuilder);
	}

	public function testFindByCategory()
	{
		$testCategoryId = 1;

		$testTicketId = 1;

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addCategory($testCategoryId);

		$result = $this->getApi()->tickets->find($criteria);

		$data = $result->getData();

		$this->assertArrayHasKey('tickets', $data);

		$matchingTickets = $data['tickets'];

		$this->assertEquals(0, count($matchingTickets));

		$ticketBuilder = $this->getApi()->tickets->createBuilder();

		$ticketBuilder->setId($testTicketId)->setCategory($testCategoryId);

		$result = $this->getApi()->tickets->save($ticketBuilder);

		$this->assertFalse(!$result->getData());

		$result = $this->getApi()->tickets->find($criteria);

		$data = $result->getData();

		$this->assertArrayHasKey('tickets', $data);

		$matchingTickets = $data['tickets'];

		$this->assertEquals(1, count($matchingTickets));

		$ticketBuilder->setId($testTicketId)->setCategory(0);

		$this->getApi()->tickets->save($ticketBuilder);
	}

	public function testFindByOrganization()
	{
		$testOrganizationId = 1;

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addOrganization($testOrganizationId);

		$result = $this->getApi()->tickets->find($criteria);

		$data = $result->getData();

		$this->assertArrayHasKey('tickets', $data);

		$matchingTickets = $data['tickets'];

		$this->assertEquals(0, count($matchingTickets));
	}
}
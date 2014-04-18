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
			$this->assertIsValidTimestamp($retrievedTicketArray[$field]);
		}

		foreach ($this->getTimestampFields('person') as $field) {
			$this->assertIsValidTimestamp($retrievedTicketArray['person'][$field]);
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
}
<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class SaveTicketTest extends AbstractApiResultTest
{
	public function testCanCreateTicket()
	{	
		$testSubject = 'Test Create Ticket';

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addSubject($testSubject);

		$result = $this->getApi()->tickets->find($criteria);

		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('tickets', $data);
		
		$this->assertEquals(count($data['tickets']), 0);
		
		$builder = $this->getApi()->tickets->createBuilder();
		
		$personBuilder = $this->getApi()->people->createPersonEditor();
		
		$personBuilder->setId(1);
		
		$builder->setSubject($testSubject)
			->setMessage('Test Ticket created for testing')
			->setCreatedBy($personBuilder);
		
		$result = $this->getApi()->tickets->save($builder);
		
		$this->assertEquals('201', $result->getResponseCode());
		
		$data = $result->getData();

		$this->assertArrayHasKey('ticket_id', $data);
		
		$newTicketId = $data['ticket_id'];

		$criteria = $this->getApi()->tickets->createCriteria();

		$this->assertInstanceOf('DeskPRO\Criteria\Ticket', $criteria);

		$criteria->addSubject($testSubject);

		$result = $this->getApi()->tickets->find($criteria);

		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('tickets', $data);

		$this->assertEquals(count($data['tickets']), 1);
		
		$this->getApi()->tickets->delete($newTicketId);
	}

	public function testCanEditTicket()
	{
		$testTicketId = 1;
		
		$result = $this->getApi()->tickets->findById($testTicketId);

		$this->assertInstanceOf('DeskPRO\Api\Result', $result);

		$this->assertArrayHasKey('ticket', $result->getData());

		$data = $result->getData();

		$retrievedTicketArray = $data['ticket'];
		
		$oldSubject = $retrievedTicketArray['subject'];
		
		$newSubject = 'Updated ticket subject';
		
		$ticketBuilder = $this->getApi()->tickets->createBuilder();
		
		$ticketBuilder->setId($testTicketId)->setSubject($newSubject);
		
		$result = $this->getApi()->tickets->save($ticketBuilder);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$result = $this->getApi()->tickets->findById($testTicketId);

		$this->assertInstanceOf('DeskPRO\Api\Result', $result);

		$this->assertArrayHasKey('ticket', $result->getData());

		$data = $result->getData();

		$retrievedTicketArray = $data['ticket'];
		
		$this->assertEquals($retrievedTicketArray['subject'], $newSubject);
		
		$ticketBuilder = $this->getApi()->tickets->createBuilder();
		
		$ticketBuilder->setId($testTicketId)->setSubject($oldSubject);
		
		$this->getApi()->tickets->save($ticketBuilder);
		
	}
}
<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class DeleteTicketTest extends AbstractApiResultTest
{
	public function testCanDeleteTicket()
	{
		$randomSubject = md5(time());
		
		$builder = $this->getApi()->tickets->createBuilder();
		
		$personBuilder = $this->getApi()->people->createPersonEditor();
		
		$personBuilder->setId(1);
		
		$builder->setSubject($randomSubject)
			->setMessage('Test Ticket created for testing')
			->setCreatedBy($personBuilder);
		
		$result = $this->getApi()->tickets->save($builder);
		
		$this->assertEquals('201', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('ticket_id', $data);
		
		$newTicketId = $data['ticket_id'];
		
		$result = $this->getApi()->tickets->delete($newTicketId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$result = $this->getApi()->tickets->findById($newTicketId);
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('ticket', $data);
		
		$retrievedTicketArray = $data['ticket'];
		
		$this->assertEquals('hidden', $retrievedTicketArray['status']);
		$this->assertEquals('deleted', $retrievedTicketArray['hidden_status']);
	}
}
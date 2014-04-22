<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class TicketActionsTest extends AbstractApiResultTest
{
	public function testCanClaimTicket()
	{
		$testTicketId = 1;
		
		$result = $this->getApi()->tickets->findById($testTicketId);
		
		$data = $result->getData();
		
		$this->assertEmpty($data['ticket']['agent']);
		
		$result = $this->getApi()->tickets->assignToMe($testTicketId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$result = $this->getApi()->tickets->findById($testTicketId);
		
		$data = $result->getData();
		
		$this->assertNotEmpty($data['ticket']['agent']);
		
		$this->assertEquals(1, $data['ticket']['agent']['id']);
		
		$builder = $this->getApi()->tickets->createBuilder();
		
		$builder->setId($testTicketId)->assignToAgent(0);
		
		$this->getApi()->tickets->save($builder);
	}
}
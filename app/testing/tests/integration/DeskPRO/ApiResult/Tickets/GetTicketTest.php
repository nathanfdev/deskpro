<?php
namespace DpUnitTests\DeskPRO\ApiResult\Tickets;
use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class GetTicketTest extends AbstractApiResultTest
{
	public function testGetTicket()
	{
		$result = $this->getApi()->tickets->findById(1);

		// TODO check that $result->getData() has the proper array
	}
}
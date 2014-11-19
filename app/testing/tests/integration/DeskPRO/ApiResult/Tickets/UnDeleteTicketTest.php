<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class UnDeleteTicketTest extends AbstractApiResultTest
{
    public function testCanUnDeleteTicket()
    {
        $testTicketId = 1;

        $result = $this->getApi()->tickets->findById($testTicketId);

        $data = $result->getData();

        $oldStatus = $data['ticket']['status'];

        $this->getApi()->tickets->delete($testTicketId);

        $result = $this->getApi()->tickets->findById($testTicketId);

        $data = $result->getData();

        $this->assertArrayHasKey('ticket', $data);

        $retrievedTicketArray = $data['ticket'];

        $this->assertEquals('hidden', $retrievedTicketArray['status']);
        $this->assertEquals('deleted', $retrievedTicketArray['hidden_status']);

        $result = $this->getApi()->tickets->unDelete($testTicketId);

        $this->assertEquals('200', $result->getResponseCode());

        $result = $this->getApi()->tickets->findById($testTicketId);

        $data = $result->getData();

        //echo $data['ticket']['status']; die;

        $this->assertEquals($oldStatus, $data['ticket']['status']);
    }
}

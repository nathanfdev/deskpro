<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckStatus;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckStatusTest extends DeskProTestCase
{
    public function _testStatus()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->status = 'awaiting_agent';

        $check = new CheckStatus('is', ['status' => 'awaiting_agent']);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckStatus('is', ['status' => 'resolved']);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testStatusCode_SupportOldStyle()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setSysId(TicketStatus::SYS_ID_DELETED);
        $deletedStatus->setId(2);
        $spamStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $spamStatus->setSysId(TicketStatus::SYS_ID_SPAM);
        $spamStatus->setId(1);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(true);
        $statusesMock->shouldReceive('findStatusOrException')->with('hidden.deleted')->andReturn($deletedStatus);
        $statusesMock->shouldReceive('findStatusOrException')->with('hidden.spam')->andReturn($spamStatus);
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withTicketStatusesMock($statusesMock)->get();

        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->status = 'hidden.deleted';

        $check = new CheckStatus('is', ['status' => 'hidden.deleted']);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckStatus('is', ['status' => 'hidden.spam']);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}

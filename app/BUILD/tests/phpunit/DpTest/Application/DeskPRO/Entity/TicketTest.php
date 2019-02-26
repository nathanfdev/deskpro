<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketDeleted;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Ticket\VirtualTicketStatus;
use DpTest\PortalTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

class TicketTest extends PortalTestCase
{
    /**
     * @var DeskproContainer
     */
    protected $containerBefore;

    public function setUp()
    {
        $this->containerBefore = App::$container;
    }

    public function tearDown()
    {
        App::$container = $this->containerBefore;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not an agent
     */
    public function testSetNotAgentForNewTicket()
    {
        $this->installDataSet('fresh', true);

        $person = new Person();
        $person->setIsAgent(false);

        $this->getEntityManager()->persist($person);

        $ticket = new Ticket();
        $ticket->setAgent($person);

        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not an agent
     */
    public function testSetNotAgentOnTicketUpdate()
    {
        $this->installDataSet('fresh', true);

        $ticket = new Ticket();
        $ticket->setSubject('subject');

        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();

        $person = new Person();
        $person->setIsAgent(false);

        $this->getEntityManager()->persist($person);

        $ticket->setAgent($person);

        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array
     */
    public function isDeleteDataProvider()
    {
        $data = [];

        $ticket1 = new Ticket();
        $status1 = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $status1->setSysId('deleted');
        $ticket1->setTicketStatus($status1);
        $data[] = [$ticket1, true];

        $ticket2 = new Ticket();
        $status2 = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $status2->setSysId('spam');
        $ticket2->setTicketStatus($status2);
        $data[] = [$ticket2, false];

        $ticket3 = new Ticket();
        $ticket3->setStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $data[] = [$ticket3, false];

        return $data;
    }

    /**
     * @dataProvider isDeleteDataProvider
     *
     * @param Ticket $ticket
     * @param bool   $expectedIsDeleted
     */
    public function testIsDeleted(Ticket $ticket, $expectedIsDeleted)
    {
        $this->assertEquals($expectedIsDeleted, $ticket->isDeleted());
    }

    public function testSetTicketStatus()
    {
        // GIVEN
        $status = new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT);
        $status->setSysId('some test');

        $ticket = new Ticket();

        // WHEN
        $ticket->setTicketStatus($status);

        // THEN
        $this->assertEquals(TicketStatus::STATUS_TYPE_AWAITING_AGENT, $ticket->getStatus());
        $this->assertEquals('some test', $ticket->getTicketStatus()->getSysId());
    }

    public function testSetTicketStatus_PendingCase()
    {
        // GIVEN
        $statusPending = new TicketStatus(TicketStatus::STATUS_TYPE_PENDING);
        $statusPending->setSysId('pending');

        $statusPending2 = new TicketStatus(TicketStatus::STATUS_TYPE_PENDING);
        $statusPending->setId(2);

        $ticket = new Ticket();
        $this->assertNull($ticket->getDateOnHold());

        // WHEN set pending
        $ticket->setTicketStatus($statusPending);

        // THEN
        $this->assertEquals(TicketStatus::STATUS_TYPE_PENDING, $ticket->getStatus());
        $this->assertNotNull($ticket->getDateOnHold());
        $dateOnHold = $ticket->getDateOnHold();

        // WHEN set sub-pending
        $ticket->setTicketStatus($statusPending2);

        // THEN
        $this->assertNotNull($ticket->getDateOnHold());
        $this->assertEquals($dateOnHold, $ticket->getDateOnHold());

        // WHEN unhold
        $ticket->setTicketStatus(new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT));

        // THEN
        $this->assertNull($ticket->getDateOnHold());
    }

    public function testSetTicketStatus_UndeleteCase()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setId(2);

        $ticketMock = $this->getMockBuilder(Ticket::class)
            ->setMethods(['undeleteTicket'])
            ->getMock();
        // check that this method called
        $ticketMock->expects($this->once())->method('undeleteTicket');
        $ticketMock->setTicketStatus($deletedStatus);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('getDeletedStatus')->andReturn($deletedStatus);
        App::$container = ContainerMock::create()->withTicketStatusesMock($statusesMock)->get();

        //WHEN/THEN
        $ticketMock->setTicketStatus(new VirtualTicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
    }

    public function getStatusCodeDataProvider()
    {
        $data = [];

        $ticket1 = new Ticket();
        $status1 = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $status1->setId(2);
        $ticket1->setTicketStatus($status1);
        $data[] = [$ticket1, TicketStatus::STATUS_TYPE_HIDDEN.'.2'];

        $ticket2 = new Ticket();
        $status2 = new VirtualTicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT);
        $ticket2->setTicketStatus($status2);
        $data[] = [$ticket2, TicketStatus::STATUS_TYPE_AWAITING_AGENT];

        return $data;
    }

    /**
     * @dataProvider getStatusCodeDataProvider
     *
     * @param Ticket $ticket
     * @param string $expectedStatusCode
     */
    public function testGetStatusCode(Ticket $ticket, $expectedStatusCode)
    {
        $this->assertEquals($expectedStatusCode, $ticket->getStatusCode());
    }

    public function testDeleteTicket()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setId(2);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('getDeletedStatus')->andReturn($deletedStatus);

        $ticketManagerMock = m::mock(TicketManager::class);
        $ticketManagerMock->shouldReceive('createAgentExecutorContext')->andReturn(m::mock(ExecutorContext::class));
        $ticketManagerMock->shouldIgnoreMissing();
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withTicketStatusesMock($statusesMock)
            ->withTicketManagerMock($ticketManagerMock)
            ->get();

        $ticketMock = $this->getMockBuilder(Ticket::class)
            ->setMethods(['getDeletionRecord'])
            ->getMock();
        $ticketMock->expects($this->once())->method('getDeletionRecord')->willReturn(null);

        // WHEN
        $ticketMock->deleteTicket();

        // THEN
        $this->assertEquals($deletedStatus, $ticketMock->getTicketStatus());
    }

    public function testUndeleteTicket()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setId(2);

        App::$container = ContainerMock::create()
            ->withNullEm()
            ->get();

        $ticketMock = $this->getMockBuilder(Ticket::class)
            ->setMethods(['getDeletionRecord'])
            ->getMock();
        $ticketMock->expects($this->once())->method('getDeletionRecord')->willReturn(new TicketDeleted());
        $ticketMock->setId(1);

        // WHEN
        $ticketMock->undeleteTicket();

        // THEN
        $this->assertEquals(TicketStatus::STATUS_TYPE_AWAITING_AGENT, $ticketMock->getStatusCode());
    }

    public function testGetStatusInt()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setId(2);
        $spamStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $spamStatus->setId(3);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('getDeletedStatus')->andReturn($deletedStatus);
        $statusesMock->shouldReceive('getSpamStatus')->andReturn($spamStatus);

        App::$container = ContainerMock::create()->withTicketStatusesMock($statusesMock)->get();

        // WHEN/THEN
        $this->assertEquals(100, Ticket::getStatusInt(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        $this->assertEquals(110, Ticket::getStatusInt(TicketStatus::STATUS_TYPE_AWAITING_USER));
        $this->assertEquals(200, Ticket::getStatusInt(TicketStatus::STATUS_TYPE_RESOLVED));
        $this->assertEquals(210, Ticket::getStatusInt(TicketStatus::STATUS_TYPE_ARCHIVED));
        $this->assertEquals(400, Ticket::getStatusInt(TicketStatus::STATUS_TYPE_PENDING));
        $this->assertEquals(310, Ticket::getStatusInt(TicketStatus::STATUS_TYPE_HIDDEN.'.2'));
        $this->assertEquals(320, Ticket::getStatusInt(TicketStatus::STATUS_TYPE_HIDDEN.'.3'));
    }

    public function testCheckStatuses()
    {
        // GIVEN
        $ticket1 = new Ticket();
        $ticket1->setTicketStatus(new VirtualTicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT));

        $ticket2 = new Ticket();
        $ticket2->setTicketStatus(new VirtualTicketStatus(TicketStatus::STATUS_TYPE_AWAITING_USER));

        $ticket3 = new Ticket();
        $ticket3->setTicketStatus(new VirtualTicketStatus(TicketStatus::STATUS_TYPE_ARCHIVED));

        $ticket4 = new Ticket();
        $ticket4->setTicketStatus(new VirtualTicketStatus(TicketStatus::STATUS_TYPE_RESOLVED));

        $ticket5 = new Ticket();
        $ticket5->setTicketStatus(new VirtualTicketStatus(TicketStatus::STATUS_TYPE_HIDDEN));

        // WHEN/THEN
        $this->assertTrue($ticket1->isAwaitingAgent());
        $this->assertFalse($ticket1->isAwaitingUser());
        $this->assertTrue($ticket1->isOpen());
        $this->assertFalse($ticket1->isResolved());
        $this->assertFalse($ticket1->getIsHidden());

        $this->assertFalse($ticket2->isAwaitingAgent());
        $this->assertTrue($ticket2->isAwaitingUser());
        $this->assertTrue($ticket2->isOpen());
        $this->assertFalse($ticket2->isResolved());
        $this->assertFalse($ticket2->getIsHidden());

        $this->assertFalse($ticket3->isAwaitingAgent());
        $this->assertFalse($ticket3->isAwaitingUser());
        $this->assertFalse($ticket3->isOpen());
        $this->assertFalse($ticket3->isResolved());
        $this->assertFalse($ticket3->getIsHidden());

        $this->assertTrue($ticket4->isResolved());
        $this->assertFalse($ticket4->getIsHidden());

        $this->assertFalse($ticket5->isResolved());
        $this->assertTrue($ticket5->getIsHidden());
    }

    public function testSetIsHold()
    {
        // GIVEN
        $ticket = new Ticket();

        // WHEN/THEN
        $ticket->setIsHold(true);
        $this->assertEquals(TicketStatus::STATUS_TYPE_PENDING, $ticket->getStatusCode());
        $ticket->setIsHold(false);
        $this->assertEquals(TicketStatus::STATUS_TYPE_AWAITING_AGENT, $ticket->getStatusCode());
    }

    public function testGetHiddenStatus()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setSysId(TicketStatus::SYS_ID_DELETED);
        $deletedStatus->setId(2);
        $spamStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $spamStatus->setSysId(TicketStatus::SYS_ID_SPAM);
        $spamStatus->setId(3);
        $agentStatus = new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT);
        $agentStatus->setId(3);

        $ticketD = new Ticket();
        $ticketD->setTicketStatus($deletedStatus);
        $ticketS = new Ticket();
        $ticketS->setTicketStatus($spamStatus);
        $ticketA = new Ticket();
        $ticketA->setTicketStatus($agentStatus);
        $ticket = new Ticket();

        // WHEN /THEN
        $this->assertEquals(TicketStatus::SYS_ID_DELETED, $ticketD->getHiddenStatus());
        $this->assertEquals(TicketStatus::SYS_ID_SPAM, $ticketS->getHiddenStatus());
        $this->assertNull($ticketA->getHiddenStatus());
        $this->assertNull($ticket->getHiddenStatus());
    }
}

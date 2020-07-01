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
use Orb\Util\Testable\DateTime;
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
        DateTime::unsetTimestampState();
    }

    public function tearDown()
    {
        App::$container = $this->containerBefore;
        DateTime::unsetTimestampState();
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

    public function testIsDeleted()
    {
        $ticket1 = new Ticket();
        $status1 = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $status1->setSysId('deleted');
        $ticket1->setTicketStatus($status1);
        $this->assertEquals(true, $ticket1->isDeleted());

        $ticket2 = new Ticket();
        $status2 = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $status2->setSysId('spam');
        $ticket2->setTicketStatus($status2);
        $this->assertEquals(false, $ticket2->isDeleted());

        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withBaseTicketStatusesMock()
            ->get();

        $ticket3 = new Ticket();
        $ticket3->setStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $this->assertEquals(false, $ticket3->isDeleted());
    }

    public function testSetTicketStatus()
    {
        // GIVEN
        $status = new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT);
        $status->setSysId('some test');

        $ticket = new Ticket();

        App::$container = ContainerMock::create()->withSettings()->get();

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

        App::$container = ContainerMock::create()->withSettings()->get();

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
        App::$container = ContainerMock::create()
            ->withSettings()
            ->withTicketStatusesMock($statusesMock)
            ->get();

        //WHEN/THEN
        $ticketMock->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
    }

    public function getStatusCodeDataProvider()
    {
        App::$container = ContainerMock::create()->withSettings()->get();
        
        $data = [];

        $ticket1 = new Ticket();
        $status1 = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $status1->setId(2);
        $ticket1->setTicketStatus($status1);
        $data[] = [$ticket1, TicketStatus::STATUS_TYPE_HIDDEN.'.2'];

        $ticket2 = new Ticket();
        $status2 = VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT);
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
            ->withSettings()
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
            ->withSettings()
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

        App::$container = ContainerMock::create()
            ->withSettings()
            ->withTicketStatusesMock($statusesMock)
            ->get();

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
        App::$container = ContainerMock::create()->withSettings()->get();
        
        $ticket1 = new Ticket();
        $ticket1->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));

        $ticket2 = new Ticket();
        $ticket2->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_USER));

        $ticket3 = new Ticket();
        $ticket3->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_ARCHIVED));

        $ticket4 = new Ticket();
        $ticket4->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_RESOLVED));

        $ticket5 = new Ticket();
        $ticket5->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_HIDDEN));

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
        App::$container = ContainerMock::create()->withSettings()->get();
        $pendingStatus = new TicketStatus(TicketStatus::STATUS_TYPE_PENDING);
        $pendingStatus->setPendingWaitingTimeMode(TicketStatus::PENDING_WAITING_TIME_MODE_USER);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('findStatusOrException')->with('pending')->andReturn($pendingStatus);
        $statusesMock->shouldReceive('findStatusOrException')->with('awaiting_agent')->andReturn(
            new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT)
        );
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withTicketStatusesMock($statusesMock)->get();


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
        App::$container = ContainerMock::create()->withSettings()->get();
        
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

    public function testChangeStatusShouldPopulateWaitingTimes()
    {
        // GIVEN
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withBaseTicketStatusesMock()->get();
        
        $pending = new TicketStatus(TicketStatus::STATUS_TYPE_PENDING);
        $pending->setPendingWaitingTimeMode(TicketStatus::PENDING_WAITING_TIME_MODE_USER);

        $ticket = new Ticket();

        // WHEN
        DateTime::setTimestampState((new \DateTime('2020-06-22 12:00:00'))->getTimestamp());
        $ticket->setTicketStatus(new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT));

        DateTime::setTimestampState((new \DateTime('2020-06-22 13:00:00'))->getTimestamp());
        $ticket->setTicketStatus(new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_USER));

        DateTime::setTimestampState((new \DateTime('2020-06-22 14:00:00'))->getTimestamp());
        $ticket->setTicketStatus($pending);

        DateTime::setTimestampState((new \DateTime('2020-06-22 15:00:00'))->getTimestamp());
        $ticket->setTicketStatus(new TicketStatus(TicketStatus::STATUS_TYPE_RESOLVED));

        // THEN
        $waitings = $ticket->getWaitingTimes();

        $this->assertCount(3, $waitings);

        $this->assertEquals(TicketStatus::STATUS_TYPE_AWAITING_AGENT, $waitings[0]['ticket_status']);
        $this->assertEquals(TicketStatus::STATUS_TYPE_AWAITING_USER, $waitings[1]['ticket_status']);
        $this->assertEquals(TicketStatus::STATUS_TYPE_PENDING, $waitings[2]['ticket_status']);

        foreach ($waitings as $waiting) {
            $this->assertArrayHasKey('start', $waiting);
            $this->assertArrayHasKey('end', $waiting);
        }
    }
}

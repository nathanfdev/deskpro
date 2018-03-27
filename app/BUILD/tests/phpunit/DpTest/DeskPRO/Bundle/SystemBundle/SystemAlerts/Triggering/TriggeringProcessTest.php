<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\TriggeringProcess;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use DpTest\DeskProTestCase;

require_once realpath(__DIR__.'/../_mocks.php');

/**
 * Class TriggeringProcessTest.
 */
class TriggeringProcessTest extends DeskProTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject Cached EM used inside the instance()
     */
    private $em;

    /**
     * @var array Events returned by the Event entity Repository
     */
    private $events = [];

    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(TriggeringProcess::class, $this->instance());
    }

    /**
     * @test
     */
    public function it_should_pass_events_to_all_triggers()
    {
        $this->events = [new MockEvent(), new MockEvent(), new MockEvent()];
        $instance     = $this->instance();
        $instance->addTrigger($trigger1 = $this->getMock(MockTrigger::class));
        $instance->addTrigger($trigger2 = $this->getMock(MockTrigger::class));

        $trigger1->expects($this->exactly(3))->method('consume');
        $trigger2->expects($this->exactly(3))->method('consume');

        $instance->run();
    }

    /**
     * @test
     */
    public function it_should_persist_incident_each_time_it_receives_an_event()
    {
        $this->events = [new MockEvent(), new MockEvent(), new MockEvent(), new MockEvent(), new MockEvent()];
        $instance     = $this->instance();
        $instance->addTrigger($trigger = new MockTrigger());

        $this->em->expects($this->exactly(5))->method('persist');
        $this->em->expects($this->once())->method('flush');

        $instance->run();
    }

    /**
     * @return TriggeringProcess
     */
    private function instance()
    {
        $query = $this
            ->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();
        $query->method('execute')->willReturn(10);

        $qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $qb->method('getQuery')->willReturn($query);
        $qb->method($this->anything())->willReturnSelf();

        $event_repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $event_repository->method('findBy')->willReturn($this->events);

        $this->em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->em->method('getRepository')->with(AbstractEvent::class)->willReturn($event_repository);
        $this->em->method('createQueryBuilder')->willReturn($qb);

        return new TriggeringProcess($this->em);
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

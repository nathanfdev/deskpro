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

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\ExceptionEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Doctrine\ORM\EntityManager;
use DpTest\DeskProTestCase;

/**
 * Class EventLoggerTest.
 */
class EventLoggerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(EventLogger::class, $this->instance());
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_should_throw_when_trying_to_log_anything_else_than_Event_or_Exception()
    {
        $this->instance()->log(new \stdClass());
    }

    /**
     * @test
     */
    public function it_should_persist_passed_Events()
    {
        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(ExceptionEvent::class));
        $em->expects($this->once())->method('flush')->with($this->isInstanceOf(ExceptionEvent::class));

        $this->instance($em)->log(new ExceptionEvent(new \Exception()));
    }

    /**
     * @test
     */
    public function it_should_log_Exceptions_as_ExceptionEvent()
    {
        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(ExceptionEvent::class));
        $em->expects($this->once())->method('flush')->with($this->isInstanceOf(ExceptionEvent::class));

        $this->instance($em)->log(new \Exception());
    }

    /**
     * @param EntityManager|null $em
     *
     * @return EventLogger
     */
    private function instance(EntityManager $em = null)
    {
        $em or $em = $this->mockEntityManager()->reveal();

        return new EventLogger($em);
    }
}

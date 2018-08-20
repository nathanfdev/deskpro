<?php

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
        $em->expects($this->once())->method('flush');

        $this->instance($em)->log(new ExceptionEvent(new \Exception()));
    }

    /**
     * @test
     */
    public function it_should_log_Exceptions_as_ExceptionEvent()
    {
        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(ExceptionEvent::class));
        $em->expects($this->once())->method('flush')->with();

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

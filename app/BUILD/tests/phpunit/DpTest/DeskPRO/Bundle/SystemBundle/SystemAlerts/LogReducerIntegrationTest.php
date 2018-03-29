<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpCriticalErrorIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\LogReducer;

include_once 'BaseIntegrationTest.php';

/**
 * Class LogReducerIntegrationTest.
 */
class LogReducerIntegrationTest extends BaseIntegrationTest
{
    const MAX_Q = 3;
    const MAX_T = 60;

    /**
     * @var LogReducer
     */
    private $reducer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->reducer = $this->get('dp_sys.alerts.log_reducer');
        $this->reducer->setQuantityLimit(self::MAX_Q);
        $this->reducer->setTimeLimit(self::MAX_T);
    }

    /**
     * @test
     */
    public function it_should_be_a_container_service()
    {
        $this->assertInstanceOf(LogReducer::class, $this->reducer);
    }

    // preProcessingReducer() on events with quantity expiration strategy ----------------------------------------------

    /**
     * @test
     */
    public function it_should_remove_new_extra_events_with_quantity_expiration_strategy()
    {
        $num = 7;
        for ($i = 0; $i < $num; ++$i) {
            $this->event_logger->log($this->dummyQuantityEvent());
        }
        $this->assertEquals($num, $this->countEvents());

        $this->reducer->preProcessingReducer();

        $this->assertEquals(self::MAX_Q, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_new_events_for_each_incident()
    {
        $num = 7;
        for ($i = 0; $i < $num; ++$i) {
            $this->event_logger->log($this->dummyQuantityEvent(1));
            $this->event_logger->log($this->dummyQuantityEvent(2));
            $this->event_logger->log($this->dummyQuantityEvent(3));
        }
        $this->assertEquals(3 * $num, $this->countEvents());

        $this->reducer->preProcessingReducer();

        $this->assertEquals(3 * self::MAX_Q, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_processed_events_with_quantity_expiration_strategy()
    {
        $new       = 7;
        $processed = 3;
        for ($i = 0; $i < $new; ++$i) {
            $this->event_logger->log($this->dummyQuantityEvent());
        }
        for ($i = 0; $i < $processed; ++$i) {
            $this->event_logger->log($this->dummyProcessedQuantityEvent());
        }
        $this->assertEquals($new + $processed, $this->countEvents());

        $this->reducer->preProcessingReducer();

        $this->assertEquals(self::MAX_Q + $processed, $this->countEvents());
    }

    // preProcessingReducer() on events with time expiration strategy --------------------------------------------------

    /**
     * @test
     */
    public function it_should_remove_new_outdated_events_with_time_expiration_strategy()
    {
        $validNum    = 2;
        $outdatedNum = 5;
        $valid       = new \DateTime();
        $outdated    = new \DateTime('-1 day');
        for ($i = 0; $i < $validNum; ++$i) {
            $this->event_logger->log($this->dummyTimeEvent(1, $valid));
        }
        for ($i = 0; $i < $outdatedNum; ++$i) {
            $this->event_logger->log($this->dummyTimeEvent(1, $outdated));
        }
        $this->assertEquals($validNum + $outdatedNum, $this->countEvents());

        $this->reducer->preProcessingReducer();

        $this->assertEquals($validNum, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_reduce_new_events_with_time_expiration_strategy_to_quantity()
    {
        $validNum    = self::MAX_Q + 5;
        $outdatedNum = 5;
        $valid       = new \DateTime();
        $outdated    = new \DateTime('-1 day');
        for ($i = 0; $i < $validNum; ++$i) {
            $this->event_logger->log($this->dummyTimeEvent(1, $valid));
        }
        for ($i = 0; $i < $outdatedNum; ++$i) {
            $this->event_logger->log($this->dummyTimeEvent(1, $outdated));
        }
        $this->assertEquals($validNum + $outdatedNum, $this->countEvents());

        $this->reducer->preProcessingReducer();

        $this->assertEquals(self::MAX_Q, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_new_events_with_time_expiration_strategy_for_each_incident()
    {
        $incidentsNum = 2;
        $validNum     = self::MAX_Q + 5;
        $outdatedNum  = 5;
        $valid        = new \DateTime();
        $outdated     = new \DateTime('-1 day');
        for ($i = 0; $i < $validNum; ++$i) {
            for ($j = 0; $j < $incidentsNum; ++$j) {
                $this->event_logger->log($this->dummyTimeEvent($j, $valid));
            }
        }
        for ($i = 0; $i < $outdatedNum; ++$i) {
            for ($j = 0; $j < $incidentsNum; ++$j) {
                $this->event_logger->log($this->dummyTimeEvent($j, $outdated));
            }
        }
        $this->assertEquals(($validNum + $outdatedNum) * $incidentsNum, $this->countEvents());

        $this->reducer->preProcessingReducer();

        $this->assertEquals(self::MAX_Q * $incidentsNum, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_processed_events_with_time_expiration_strategy()
    {
        $validNum     = 2;
        $outdatedNum  = 5;
        $processedNum = 10;
        $valid        = new \DateTime();
        $outdated     = new \DateTime('-1 day');
        for ($i = 0; $i < $validNum; ++$i) {
            $this->event_logger->log($this->dummyTimeEvent(1, $valid));
        }
        for ($i = 0; $i < $outdatedNum; ++$i) {
            $this->event_logger->log($this->dummyTimeEvent(1, $outdated));
        }
        for ($i = 0; $i < $processedNum; ++$i) {
            $this->event_logger->log($this->dummyProcessedTimeEvent(1, [$valid, $outdated][$i % 2]));
        }
        $this->assertEquals($validNum + $outdatedNum + $processedNum, $this->countEvents());

        $this->reducer->preProcessingReducer();

        $this->assertEquals($validNum + $processedNum, $this->countEvents());
    }

    // postProcessingReducer() on events with quantity expiration strategy ---------------------------------------------

    /**
     * @test
     */
    public function it_should_remove_processed_extra_events_with_quantity_expiration_strategy()
    {
        $num = 7;
        for ($i = 0; $i < $num; ++$i) {
            $this->event_logger->log($this->dummyProcessedQuantityEvent());
        }
        $this->assertEquals($num, $this->countEvents());

        $this->reducer->postProcessingReducer();

        $this->assertEquals(self::MAX_Q, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_processed_events_for_each_incident()
    {
        $num = 7;
        for ($i = 0; $i < $num; ++$i) {
            $this->event_logger->log($this->dummyProcessedQuantityEvent(1));
            $this->event_logger->log($this->dummyProcessedQuantityEvent(2));
            $this->event_logger->log($this->dummyProcessedQuantityEvent(3));
        }
        $this->assertEquals(3 * $num, $this->countEvents());

        $this->reducer->postProcessingReducer();

        $this->assertEquals(3 * self::MAX_Q, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_new_events_with_quantity_expiration_strategy()
    {
        $processed = 7;
        $new       = 3;
        for ($i = 0; $i < $processed; ++$i) {
            $this->event_logger->log($this->dummyProcessedQuantityEvent());
        }
        for ($i = 0; $i < $new; ++$i) {
            $this->event_logger->log($this->dummyQuantityEvent());
        }
        $this->assertEquals($processed + $new, $this->countEvents());

        $this->reducer->postProcessingReducer();

        $this->assertEquals(self::MAX_Q + $new, $this->countEvents());
    }

    // postProcessingReducer() on events with time expiration strategy -------------------------------------------------

    /**
     * @test
     */
    public function it_should_preserve_processed_outdated_events_with_time_expiration_strategy()
    {
        $this->event_logger->log($this->dummyProcessedTimeEvent(1, new \DateTime('-2 days')));
        $this->event_logger->log($this->dummyProcessedTimeEvent(1, new \DateTime('-5 hours')));
        $this->event_logger->log($this->dummyProcessedTimeEvent(1, new \DateTime('now')));
        $this->assertEquals(3, $this->countEvents());

        $this->reducer->postProcessingReducer();

        $this->assertEquals(3, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_reduce_processed_events_with_time_expiration_strategy_to_quantity()
    {
        $total    = self::MAX_Q + 5;
        $valid    = new \DateTime();
        $outdated = new \DateTime('-1 day');
        for ($i = 0; $i < $total; ++$i) {
            $this->event_logger->log($this->dummyProcessedTimeEvent(1, [$valid, $outdated][$i % 2]));
        }
        $this->assertEquals($total, $this->countEvents());

        $this->reducer->postProcessingReducer();

        $this->assertEquals(self::MAX_Q, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_processed_events_with_time_expiration_strategy_for_each_incident()
    {
        $incidentsNum = 2;
        $total        = self::MAX_Q + 5;
        $valid        = new \DateTime();
        $outdated     = new \DateTime('-1 day');
        for ($i = 0; $i < $total; ++$i) {
            for ($j = 0; $j < $incidentsNum; ++$j) {
                $this->event_logger->log($this->dummyProcessedTimeEvent($j, [$valid, $outdated][$i % 2]));
            }
        }
        $this->assertEquals($total * $incidentsNum, $this->countEvents());

        $this->reducer->postProcessingReducer();

        $this->assertEquals(self::MAX_Q * $incidentsNum, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_new_events_with_time_expiration_strategy()
    {
        $processedNum = self::MAX_Q + 7;
        $newNum       = 10;
        $valid        = new \DateTime();
        $outdated     = new \DateTime('-1 day');
        for ($i = 0; $i < $processedNum; ++$i) {
            $this->event_logger->log($this->dummyProcessedTimeEvent(1, [$valid, $outdated][$i % 2]));
        }
        for ($i = 0; $i < $newNum; ++$i) {
            $this->event_logger->log($this->dummyTimeEvent(1, [$valid, $outdated][$i % 2]));
        }
        $this->assertEquals($processedNum + $newNum, $this->countEvents());

        $this->reducer->postProcessingReducer();

        $this->assertEquals(self::MAX_Q + $newNum, $this->countEvents());
    }

    /**
     * @test
     */
    public function it_should_preserve_the_oldest_and_the_newest_events_with_time_expiration_strategy()
    {
        // for now it turned off
        return;
        $this->reducer->setQuantityLimit(2);
        $this->event_logger->log($this->dummyQuantityEvent(1, $oldest = new \DateTime('-1 year')));
        for ($i = 1; $i <= 10; ++$i) {
            $daysAgo = 20 - $i;
            $this->event_logger->log($this->dummyQuantityEvent(1, new \DateTime("-$daysAgo days")));
        }
        $this->event_logger->log($this->dummyQuantityEvent(1, $newest = new \DateTime('now')));
        $this->triggering_process->run(100);
        $this->assertEquals(12, $this->countEvents());
        $this->assertEquals(1, $this->countAllIncidents());

        $this->reducer->postProcessingReducer();

        $events = $this->sysEm()->getRepository(AbstractEvent::class)->findAll();
        $this->assertCount(2, $events);
        $min = min($events[0]->getDateCreated(), $events[1]->getDateCreated());
        $max = max($events[0]->getDateCreated(), $events[1]->getDateCreated());
        $this->assertEquals($min, $oldest);
        $this->assertEquals($max, $newest);
    }

    // Common behaviour ------------------------------------------------------------------------------------------------

    public function it_should_preserve_events_referenced_as_first_and_last_failures()
    {
        $this->event_logger->log($first = $this->dummyQuantityEvent());
        $this->event_logger->log($second = $this->dummyQuantityEvent());
        for ($i = 0; $i < 10; ++$i) {
            $this->event_logger->log($this->dummyQuantityEvent());
        }
        $this->event_logger->log($last = $this->dummyQuantityEvent());
        $incident = new PhpCriticalErrorIncident();
        $incident->addEvent($first);
        $incident->addEvent($second);
        $this->sysEm()->persist($incident);
        $this->sysEm()->flush();

        $this->reducer->setQuantityLimit(3);
        $this->reducer->preProcessingReducer();
        $this->reducer->postProcessingReducer();

        $events = $this->sysEm()->getRepository(AbstractEvent::class)->findAll();
        $this->assertCount(3, $events);
        $this->assertEquals($first, $events[0]);
        $this->assertEquals($second, $events[1]);
        $this->assertEquals($last, $events[2]);
    }

    // Helpers ---------------------------------------------------------------------------------------------------------

    private function sysEm()
    {
        return $this->get('doctrine.orm.system_entity_manager');
    }

    private function dummyQuantityEvent($num = 1, $date = null)
    {
        $event = new ErrorEvent(E_ERROR, 'test', 'test.php', $num);
        if ($date) {
            $event->setDateCreated($date);
        }

        return $event;
    }

    private function dummyTimeEvent($num = 1, $date = null)
    {
        $event = new OutgoingEmailFailureEvent($num, '1@1.lo', new RawTransportException());
        if ($date) {
            $event->setDateCreated($date);
        }

        return $event;
    }

    private function dummyProcessedQuantityEvent($num = 1, $date = null)
    {
        $event = new ErrorEvent(E_ERROR, 'test', 'test.php', $num);
        $event->setProcessed(true);
        if ($date) {
            $event->setDateCreated($date);
        }

        return $event;
    }

    private function dummyProcessedTimeEvent($num = 1, $date = null)
    {
        $event = new OutgoingEmailFailureEvent($num, '1@1.lo', new RawTransportException());
        $event->setProcessed(true);
        if ($date) {
            $event->setDateCreated($date);
        }

        return $event;
    }
}

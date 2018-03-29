<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\OutgoingEmailFailureIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\TriggeringProcess;

require_once realpath(__DIR__.'/../BaseIntegrationTest.php');

/**
 * Class TriggeringProcessIntegrationTest.
 */
class TriggeringProcessIntegrationTest extends BaseIntegrationTest
{
    /**
     * @test
     */
    public function it_should_be_a_container_service()
    {
        $this->assertInstanceOf(TriggeringProcess::class, $this->triggering_process);
    }

    /**
     * @test
     */
    public function it_should_collect_tagged_trigger_services()
    {
        $this->assertNotEmpty($triggers = $this->triggering_process->getTriggers());
        foreach ($triggers as $trigger) {
            $this->assertInstanceOf(Trigger::class, $trigger);
        }
    }

    /**
     * @test
     */
    public function it_should_add_failure_events_to_the_existing_resolved_incidents()
    {
        // Given a resolved incident
        $incident = $this->givenResolvedIncident($numFailures = 3);

        // When adding a new failure event
        $this->event_logger->log($e = new OutgoingEmailFailureEvent(1, 'test@despro.dev', new RawTransportException()));
        $this->triggering_process->run();

        // Then the failure should be associated with the incident and it should still be resolved
        $this->assertEquals(1, $this->countAllIncidents());
        $this->assertEquals($numFailures + 2, $incident->getEventsCount());
        $this->assertTrue($incident->isResolved());
        $this->assertEquals($e, $incident->getFirstFailureEvent());
        $this->assertEquals($e, $incident->getLastFailureEvent());
    }

    /**
     * @test
     */
    public function it_should_reopen_resolved_incidents_if_new_errors_occur()
    {
        // Given a resolved incident
        $incident = $this->givenResolvedIncident($numFailures = 3);

        // When adding a new failure events
        $this->event_logger->log($first = new OutgoingEmailFailureEvent(
            1, 'test@despro.dev', new RawTransportException(), new \DateTime('-23 hours')
        ));
        $this->event_logger->log($second = new OutgoingEmailFailureEvent(
            1, 'test@despro.dev', new RawTransportException()
        ));
        $this->triggering_process->run();

        // Then the failure should be associated with the incident and it should be continuing
        $this->assertEquals(1, $this->countAllIncidents());
        $this->assertEquals($numFailures + 3, $incident->getEventsCount());
        $this->assertFalse($incident->isResolved());
        $this->assertEquals($first, $incident->getFirstFailureEvent());
        $this->assertEquals($second, $incident->getLastFailureEvent());
    }

    /**
     * @param int $numFailures
     *
     * @return StatefulIncident
     */
    private function givenResolvedIncident($numFailures)
    {
        for ($i = $numFailures; $i >= 1; --$i) {
            $this->event_logger->log(new OutgoingEmailFailureEvent(
                1, 'test@despro.dev', new RawTransportException(), new \DateTime('-'.($i * 10).' days')
            ));
        }
        $this->event_logger->log(new OutgoingEmailSuccessEvent(1, 'test@despro.dev'));
        $this->triggering_process->run();
        $incident = $this->findSingleIncident();
        $this->assertInstanceOf(OutgoingEmailFailureIncident::class, $incident);
        $this->assertEquals($numFailures + 1, $incident->getEventsCount());
        $this->assertTrue($incident->isResolved());

        return $incident;
    }
}

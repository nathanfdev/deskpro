<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

require_once realpath(__DIR__.'/../_mocks.php');
require_once 'TriggerTest.php';

/**
 * Class StatefulIncidentTriggerTest.
 *
 * Note that this extends another TriggerTest to verify the same behaviour as some of the methods get overridden.
 */
class StatefulIncidentTriggerTest extends TriggerTest
{
    /**
     * @test
     */
    public function it_should_call_resolved_callback_when_a_continuing_incident_no_longer_meets_incident_criteria()
    {
        $called_times = 0;
        $trigger      = new MockStatefulIncidentTrigger();
        $trigger->setContinuingIncidents([$this->createRaisedIncident()]);
        $trigger->setResolvedCallback(function () use (&$called_times) {
            ++$called_times;
        });

        $trigger->consume(new MockSuccessEvent(false, 'test'));

        $this->assertEquals(1, $called_times);
    }

    /**
     * @test
     */
    public function it_should_call_closed_callback_when_a_continuing_incident_no_longer_meets_incident_criteria()
    {
        $called_times = 0;
        $trigger      = new MockStatefulIncidentTrigger();
        $trigger->setContinuingIncidents([$this->createRaisedIncident()]);
        $trigger->setClosedCallback(function () use (&$called_times) {
            ++$called_times;
        });

        $trigger->consume(new MockSuccessEvent(false, 'test'));

        $this->assertEquals(1, $called_times);
    }

    /**
     * @test
     */
    public function it_should_call_continuing_callback_for_each_new_event_while_incident_criteria_are_met()
    {
        $called_times = 0;
        $trigger      = new MockStatefulIncidentTrigger();
        $trigger->setContinuingIncidents([$this->createRaisedIncident()]);
        $trigger->setContinuingCallback(function () use (&$called_times) {
            ++$called_times;
        });

        for ($i = 0; $i < 3; ++$i) {
            $trigger->consume(new MockEvent(false, 'test'));
        }

        $this->assertEquals(3, $called_times);
    }

    /**
     * @return MockIncident
     */
    private function createRaisedIncident()
    {
        $incident = new MockIncident();
        for ($i = 0; $i < 5; ++$i) {
            $incident->addEvent(new MockEvent(false, 'test'));
        }
        $incident->setRaised(true);

        return $incident;
    }
}

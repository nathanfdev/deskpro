<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

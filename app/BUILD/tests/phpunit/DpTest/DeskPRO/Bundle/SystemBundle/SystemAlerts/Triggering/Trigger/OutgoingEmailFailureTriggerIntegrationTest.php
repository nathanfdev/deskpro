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

namespace DpTest\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Email\OutgoingEmailFailureTrigger;
use DpTest\Bundle\SystemBundle\SystemAlerts\BaseIntegrationTest;

require_once realpath(__DIR__.'/../../BaseIntegrationTest.php');

/**
 * Class OutgoingEmailFailureTriggerIntegrationTest.
 */
class OutgoingEmailFailureTriggerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var OutgoingEmailFailureTrigger
     */
    private $trigger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->trigger = $this->get('dp_sys.alerts.outgoing_email_failure_trigger');
    }

    /**
     * @test
     */
    public function it_should_count_OutgoingEmailFailure_events()
    {
        $this->event_logger->log($this->dummyFailure());
        $this->event_logger->log($this->dummyFailure());
        $this->event_logger->log($this->dummyFailure());

        $this->triggering_process->run();

        $this->assertCount(3, $this->trigger->getState()['failing_event_ids']);
    }

    /**
     * @test
     */
    public function it_should_flush_state_after_OutgoingEmailSuccess_event()
    {
        $initial_trigger_state = (new OutgoingEmailFailureTrigger($this->em))->getState();

        $this->event_logger->log($this->dummyFailure());
        $this->assertNotEquals($initial_trigger_state, $this->trigger->getState());

        $this->event_logger->log($this->dummySuccess());
        $this->triggering_process->run();

        $this->assertEquals($initial_trigger_state, $this->trigger->getState());
    }

    /**
     * @test
     */
    public function it_should_create_an_incident_when_failing_for_more_than_the_allowed_interval()
    {
        $this->assertEquals(0, $this->countIncidents());
        $this->trigger->setSilenceTime(7);
        $this->event_logger->log($this->dummyFailure('-10 minutes'));
        $this->event_logger->log($this->dummyFailure('now'));

        $this->triggering_process->run();

        $this->assertEquals(1, $this->countIncidents());
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_when_success_split_failures_for_periods_shorter_than_the_allowed()
    {
        $this->assertEquals(0, $this->countIncidents());
        $this->trigger->setSilenceTime(7);
        $this->event_logger->log($this->dummyFailure('-10 minutes'));
        $this->event_logger->log($this->dummySuccess('-5 minutes'));
        $this->event_logger->log($this->dummyFailure('now'));

        $this->triggering_process->run();

        $this->assertEquals(0, $this->countIncidents());
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $when
     *
     * @return OutgoingEmailFailureEvent
     */
    private function dummyFailure($when = 'now')
    {
        return new OutgoingEmailFailureEvent(new RawTransportException(), new \DateTime($when));
    }

    /**
     * @param string $when
     *
     * @return OutgoingEmailSuccessEvent
     */
    private function dummySuccess($when = 'now')
    {
        return new OutgoingEmailSuccessEvent(new \DateTime($when));
    }
}

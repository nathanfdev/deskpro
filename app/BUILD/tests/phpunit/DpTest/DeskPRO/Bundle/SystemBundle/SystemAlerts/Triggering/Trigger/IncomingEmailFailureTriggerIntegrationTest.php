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

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Email\IncomingEmailFailureTrigger;
use DpTest\Bundle\SystemBundle\SystemAlerts\BaseIntegrationTest;
use Zend\Mail\Exception\RuntimeException;

require_once realpath(__DIR__.'/../../BaseIntegrationTest.php');

/**
 * Class IncomingEmailFailureTriggerIntegrationTest.
 */
class IncomingEmailFailureTriggerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var IncomingEmailFailureTrigger
     */
    private $trigger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->trigger = $this->get('dp_sys.alerts.incoming_email_failure_trigger');
    }

    /**
     * @test
     */
    public function it_should_count_IncomingEmailFailure_events()
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
    public function it_should_flush_state_after_IncomingEmailSuccess_event()
    {
        $initial_trigger_state = (new IncomingEmailFailureTrigger($this->em))->getState();

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
     * @return IncomingEmailFailureEvent
     */
    private function dummyFailure($when = 'now')
    {
        return new IncomingEmailFailureEvent(new RuntimeException(), new \DateTime($when));
    }

    /**
     * @param string $when
     *
     * @return IncomingEmailSuccessEvent
     */
    private function dummySuccess($when = 'now')
    {
        return new IncomingEmailSuccessEvent(new \DateTime($when));
    }
}

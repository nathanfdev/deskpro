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

namespace DpTest\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpNoticeIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\PHP\PhpNoticeTrigger;
use DpTest\Bundle\SystemBundle\SystemAlerts\BaseIntegrationTest;

require_once realpath(__DIR__.'/../../../BaseIntegrationTest.php');

/**
 * Class PhpNoticeTriggerIntegrationTest.
 */
class PhpNoticeTriggerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var PhpNoticeTrigger
     */
    protected $trigger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->trigger = $this->get('dp_sys.alerts.php_notice_trigger');
        $this->trigger->setIncidentErrorsCount(3);
        $this->trigger->setPeriodMinutes(60 * 24);
    }

    /**
     * @test
     */
    public function it_should_create_an_incident_for_3_errors_that_appeared_within_last_day()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->event_logger->log($this->dummyNotice('Message 1', 'test.php', 1, '-20 hours'));
        $this->event_logger->log($this->dummyNotice('Message 2', 'test.php', 1, '-10 hours'));
        $this->event_logger->log($this->dummyNotice('Message 3', 'test.php', 1, 'now'));
        $this->triggering_process->run();
        $this->assertEquals(1, $this->countIncidents(PhpNoticeIncident::class));
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_for_only_2_errors_that_appeared_within_last_day()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->event_logger->log($this->dummyNotice('Message 2', 'test.php', 1, '-10 hours'));
        $this->event_logger->log($this->dummyNotice('Message 3', 'test.php', 1, 'now'));
        $this->triggering_process->run();
        $this->assertEquals(0, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_for_3_errors_that_appeared_more_than_a_month_ago()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->event_logger->log($this->dummyNotice('Message 1', 'test.php', 1, '-50 days'));
        $this->event_logger->log($this->dummyNotice('Message 2', 'test.php', 1, '-45 days'));
        $this->event_logger->log($this->dummyNotice('Message 3', 'test.php', 1, '-40 days'));
        $this->triggering_process->run();
        $this->assertEquals(0, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_for_PHP_fatal_error()
    {
        $this->event_logger->log(new ErrorEvent(E_ERROR, 'test', 'test.php', 1));
        $this->event_logger->log(new ErrorEvent(E_ERROR, 'test', 'test.php', 1));
        $this->event_logger->log(new ErrorEvent(E_ERROR, 'test', 'test.php', 1));
        $this->triggering_process->run();
        $this->assertEquals(0, $this->countIncidents(PhpNoticeIncident::class));
    }

    /**
     * @test
     */
    public function it_should_group_events_into_incidents_by_error_code_and_file_and_line()
    {
        $this->assertEquals(0, $this->countAllIncidents());
        $this->event_logger->log($this->dummyNotice('Message 1', 'test.php', 1));
        $this->event_logger->log($this->dummyNotice('Message 2', 'test.php', 1));
        $this->event_logger->log($this->dummyNotice('Message 3', 'test.php', 1));
        $this->event_logger->log($this->dummyNotice('Message 4', 'test-2.php', 2));

        $this->triggering_process->run();

        $this->assertEquals(2, $this->countIncidents(PhpNoticeIncident::class));
    }

    /**
     * @test
     */
    public function it_should_update_continuing_incident_with_new_errors()
    {
        // Given an incident
        $this->event_logger->log($this->dummyNotice());
        $this->event_logger->log($this->dummyNotice());
        $this->event_logger->log($this->dummyNotice());
        $this->triggering_process->run();
        $incident = $this->findSingleIncident();
        $this->assertEquals(1, $this->countRaisedIncidents());
        $this->assertCount(3, $incident->getEvents());

        // When adding a new error
        $this->event_logger->log($this->dummyNotice());
        $this->triggering_process->run();

        // Then there should be still 1 incident, but related to 4 events
        $this->assertEquals(1, $this->countIncidents(PhpNoticeIncident::class));
        $this->assertCount(4, $incident->getEvents());
    }

    /**
     * @test
     */
    public function it_should_update_a_dismissed_incident_when_new_error_occurs()
    {
        // Given a dismissed incident
        $this->event_logger->log($this->dummyNotice());
        $this->event_logger->log($this->dummyNotice());
        $this->event_logger->log($this->dummyNotice());
        $this->triggering_process->run();
        $incident = $this->findSingleIncident();
        $this->assertTrue($incident->isRaised());
        $this->assertCount(3, $incident->getEvents());
        $incident->setDismissed(true);
        $this->em->persist($incident);
        $this->em->flush();

        // When adding a new error
        $this->event_logger->log($this->dummyNotice());
        $this->event_logger->log($this->dummyNotice());
        $this->event_logger->log($this->dummyNotice());
        $this->triggering_process->run();

        // Then
        $this->assertEquals(1, $this->countAllIncidents());
        $this->assertCount(6, $incident->getEvents());
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param string $date
     *
     * @return ErrorEvent
     */
    protected function dummyNotice($message = 'Test error', $file = 'error.php', $line = 1, $date = 'now')
    {
        $event = new ErrorEvent(E_NOTICE, $message, $file, $line, new \DateTime($date));
        $this->em->persist($event);
        $this->em->flush($event);

        return $event;
    }
}

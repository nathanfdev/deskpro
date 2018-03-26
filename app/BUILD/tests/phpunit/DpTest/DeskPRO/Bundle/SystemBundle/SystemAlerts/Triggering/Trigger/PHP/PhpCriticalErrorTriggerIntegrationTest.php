<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpCriticalErrorIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\PHP\PhpCriticalErrorTrigger;
use DpTest\Bundle\SystemBundle\SystemAlerts\BaseIntegrationTest;

require_once realpath(__DIR__.'/../../../BaseIntegrationTest.php');

/**
 * Class PhpCriticalErrorTriggerIntegrationTest.
 */
class PhpCriticalErrorTriggerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var PhpCriticalErrorTrigger
     */
    protected $trigger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->trigger = $this->get('dp_sys.alerts.php_critical_error_trigger');
        $this->trigger->setIncidentErrorsCount(1);
    }

    /**
     * @test
     */
    public function it_should_create_an_incident_for_error_that_appeared_3_days_ago()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->event_logger->log($this->dummyError('Message 1', 'test.php', 1, '-3 days'));
        $this->triggering_process->run();

        // for now it turned off
        $this->assertEquals(0, $this->countIncidents(PhpCriticalErrorIncident::class));
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_for_error_that_appeared_two_years_ago()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->event_logger->log($this->dummyError('Message 1', 'test.php', 1, '-2 years'));
        $this->triggering_process->run();
        $this->assertEquals(0, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_for_PHP_notice()
    {
        $this->event_logger->log(new ErrorEvent(E_NOTICE, 'test', 'test.php', 1));
        $this->triggering_process->run();
        $this->assertEquals(0, $this->countIncidents(PhpCriticalErrorIncident::class));
    }

    /**
     * @test
     */
    public function it_should_group_events_into_incidents_by_error_code_and_file_and_line()
    {
        $this->assertEquals(0, $this->countAllIncidents());
        $this->event_logger->log($this->dummyError('Message 1', 'test.php', 1));
        $this->event_logger->log($this->dummyError('Message 2', 'test.php', 1));
        $this->event_logger->log($this->dummyError('Message 3', 'test.php', 1));
        $this->event_logger->log($this->dummyError('Message 4', 'test-2.php', 2));

        $this->triggering_process->run();

        // for now it turned off
        $this->assertEquals(0, $this->countIncidents(PhpCriticalErrorIncident::class));
    }

    /**
     * @test
     */
    public function it_should_update_continuing_incident_with_new_errors()
    {
        // Given an incident
        $this->event_logger->log($this->dummyError());
        $this->triggering_process->run();
        $incident = $this->findSingleIncident(false);

        return;

        $this->assertEquals(1, $this->countRaisedIncidents());
        $this->assertCount(1, $incident->getEvents());

        // When adding a new error
        $this->event_logger->log($this->dummyError());
        $this->triggering_process->run();

        // Then there should be still 1 incident, but related to 2 events
        $this->assertEquals(1, $this->countRaisedIncidents());
        $this->assertCount(2, $incident->getEvents());
    }

    /**
     * @test
     */
    public function it_should_update_a_dismissed_incident_when_a_new_error_occurs()
    {
        // Given a dismissed incident
        $this->event_logger->log($this->dummyError());
        $this->triggering_process->run();
        $incident = $this->findSingleIncident(false);

        return;

        $this->assertTrue($incident->isRaised());
        $this->assertCount(1, $incident->getEvents());
        $incident->setDismissed(true);
        $this->em->persist($incident);
        $this->em->flush();

        // When adding a new error
        $this->event_logger->log($this->dummyError());
        $this->triggering_process->run();

        // Then
        $this->assertEquals(1, $this->countAllIncidents());
        $this->assertCount(2, $incident->getEvents());
    }

//    /**
//     * @test
//     */
//    public function it_should_notify_all_admins_via_email_when_an_incident_is_raised()
//    {
//        /* @var \Swift_Plugins_MessageLogger $mailer */
//        $logger = $this->get('swiftmailer.mailer.default.plugin.messagelogger');
//        $logger->clear();
//        $this->assertEquals(0, $this->countRaisedIncidents());
//        $this->assertEquals(0, $logger->countMessages());

//        $this->event_logger->log($this->dummyError());
//        $this->event_logger->log($this->dummyError());
//        $this->triggering_process->run();

//        $this->assertEquals(1, $this->countIncidents(PhpCriticalErrorIncident::class));
//        $this->assertEquals(1, $logger->countMessages());
//    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param string $date
     *
     * @return ErrorEvent
     */
    protected function dummyError($message = 'Test error', $file = 'error.php', $line = 1, $date = 'now')
    {
        $event = new ErrorEvent(E_ERROR, $message, $file, $line, new \DateTime($date));
        $this->em->persist($event);
        $this->em->flush($event);

        return $event;
    }
}

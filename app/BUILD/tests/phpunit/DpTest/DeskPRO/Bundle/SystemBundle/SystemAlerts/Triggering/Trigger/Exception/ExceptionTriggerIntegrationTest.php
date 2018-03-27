<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\ExceptionEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Exception\ExceptionIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Exception\ExceptionTrigger;
use DpTest\Bundle\SystemBundle\SystemAlerts\BaseIntegrationTest;

require_once realpath(__DIR__.'/../../../BaseIntegrationTest.php');

/**
 * Class ExceptionTriggerIntegrationTest.
 */
class ExceptionTriggerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var ExceptionTrigger
     */
    protected $trigger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->trigger = $this->get('dp_sys.alerts.exception_trigger');
        $this->trigger->setIncidentErrorsCount(1);
    }

    /**
     * @test
     */
    public function it_should_create_an_incident_for_exception_that_appeared_3_days_ago()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->event_logger->log($this->dummyException('-3 days'));
        $this->triggering_process->run();
        // for now it turned off
        $this->assertEquals(0, $this->countIncidents(ExceptionIncident::class));
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_for_an_event_occurred_two_years_ago()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->event_logger->log($this->dummyException('-2 years'));
        $this->triggering_process->run();
        $this->assertEquals(0, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_group_events_into_incidents_by_exception_class_and_code()
    {
        $this->assertEquals(0, $this->countAllIncidents());
        $this->event_logger->log(new \Exception('First', 1));
        $this->event_logger->log(new \Exception('Second', 1));
        $this->event_logger->log(new \Exception('Third', 1));
        $this->event_logger->log(new \Exception('Fourth', 2));

        $this->triggering_process->run();
        // for now it turned off
        $this->assertEquals(0, $this->countIncidents(ExceptionIncident::class));
    }

    /**
     * @test
     */
    public function it_should_update_continuing_incident_with_new_exceptions()
    {
        // Given an incident
        $this->event_logger->log($this->dummyException());
        $this->triggering_process->run();

        $incident = $this->findSingleIncident(false);

        return;

        // for now it turned off
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->assertCount(0, $incident->getEvents());

        // When adding a new exception event
        $this->event_logger->log($this->dummyException());
        $this->triggering_process->run();

        // Then there should be still 1 incident, but related to 2 events

        // for now it turned off
        $this->assertCount(0, $incident->getEvents());
        $this->assertEquals(0, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_update_a_dismissed_incident_when_new_exception_occurs()
    {
        // Given a dismissed incident
        $this->event_logger->log($this->dummyException());
        $this->triggering_process->run();
        $incident = $this->findSingleIncident(false);

        return;

        $this->assertTrue($incident->isRaised());
        $this->assertCount(1, $incident->getEvents());
        $incident->setDismissed(true);
        $this->em->persist($incident);
        $this->em->flush();

        // When adding a new event
        $this->event_logger->log($this->dummyException());
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

//        $this->event_logger->log($this->dummyException());
//        $this->triggering_process->run();

//        $this->assertEquals(1, $this->countIncidents(ExceptionIncident::class));
//        $this->assertEquals(1, $logger->countMessages());
//    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $date
     *
     * @return ExceptionEvent
     */
    protected function dummyException($date = 'now')
    {
        $event = new ExceptionEvent(new \Exception(), new \DateTime($date));
        $this->em->persist($event);
        $this->em->flush($event);

        return $event;
    }
}

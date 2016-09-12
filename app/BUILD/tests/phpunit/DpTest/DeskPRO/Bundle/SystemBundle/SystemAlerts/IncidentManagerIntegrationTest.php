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

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\IncomingEmailFailureIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\IncidentManager;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Email\IncomingEmailFailureTrigger;
use Zend\Mail\Exception\RuntimeException;

include_once 'BaseIntegrationTest.php';

/**
 * Class IncidentManagerIntegrationTest.
 */
class IncidentManagerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var IncomingEmailFailureTrigger
     */
    private $trigger;

    /**
     * @var IncidentManager
     */
    private $incident_manager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->incident_manager = $this->get('dp_sys.alerts.incident_manager');
        $this->trigger          = $this->get('dp_sys.alerts.incoming_email_failure_trigger');
    }

    /**
     * @test
     */
    public function it_should_be_a_container_service()
    {
        $this->assertInstanceOf(IncidentManager::class, $this->incident_manager);
    }

    /**
     * @test
     */
    public function it_should_mark_Incident_dismissed_and_save_it()
    {
        $incident = new IncomingEmailFailureIncident();
        $this->assertFalse($incident->isDismissed());

        $this->incident_manager->dismiss($incident);

        $this->assertTrue($incident->isDismissed());
        $this->assertNotNull($incident->getId());
    }

    /**
     * @test
     */
    public function it_should_call_dismissed_callback()
    {
        $incident     = new IncomingEmailFailureIncident();
        $called_times = 0;
        $this->trigger->setDismissedCallback(function () use (&$called_times) {
            ++$called_times;
        });

        $this->incident_manager->dismiss($incident);

        $this->assertEquals(1, $called_times);
    }

    /**
     * @test
     */
    public function it_should_call_closed_callback()
    {
        $incident     = new IncomingEmailFailureIncident();
        $called_times = 0;
        $this->trigger->setClosedCallback(function () use (&$called_times) {
            ++$called_times;
        });

        $this->incident_manager->dismiss($incident);

        $this->assertEquals(1, $called_times);
    }

    /**
     * @test
     */
    public function it_should_remove_incidents_and_related_events()
    {
        $email = $this->createDummyEmail();
        $this->event_logger->log(new IncomingEmailFailureEvent($email, new RuntimeException()));
        $this->event_logger->log($e1 = new IncomingEmailFailureEvent($email, new RuntimeException()));
        $this->event_logger->log($e2 = new IncomingEmailFailureEvent($email, new RuntimeException()));
        $incident = new IncomingEmailFailureIncident();
        $incident->setEvents([$e1, $e2]);
        $this->em->persist($incident);
        $this->em->flush();
        $this->assertEquals(3, $this->countEvents());
        $this->assertEquals(1, $this->countAllIncidents());

        $this->incident_manager->remove($incident);

        $this->assertEquals(1, $this->countEvents());
        $this->assertEquals(0, $this->countAllIncidents());
    }

    /**
     * @return EmailAccount
     */
    private function createDummyEmail()
    {
        $email          = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $email->id      = 1;
        $email->address = uniqid().'@email.lo';

        return $email;
    }
}

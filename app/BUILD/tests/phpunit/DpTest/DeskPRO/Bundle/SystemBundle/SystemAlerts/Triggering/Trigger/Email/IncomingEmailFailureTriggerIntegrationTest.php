<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger;

use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Email\IncomingEmailFailureTrigger;
use DpTest\Bundle\SystemBundle\SystemAlerts\BaseIntegrationTest;
use Zend\Mail\Exception\RuntimeException;

require_once realpath(__DIR__.'/../../../BaseIntegrationTest.php');

/**
 * Class IncomingEmailFailureTriggerIntegrationTest.
 */
class IncomingEmailFailureTriggerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var IncomingEmailFailureTrigger
     */
    protected $trigger;

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
    public function it_should_group_events_into_incidents_by_email_account_id()
    {
        $this->assertEquals(0, $this->countAllIncidents());
        $this->event_logger->log($this->dummyFailure('-20 minutes', 1));
        $this->event_logger->log($this->dummyFailure('-19 minutes', 2));
        $this->event_logger->log($this->dummySuccess('-15 minutes', 3));
        $this->event_logger->log($this->dummyFailure('-10 minutes', 1));
        $this->event_logger->log($this->dummySuccess('-5 minutes', 2));
        $this->event_logger->log($this->dummyFailure('now', 1));

        $this->triggering_process->run();

        $this->assertEquals(3, $this->countAllIncidents());
    }

    /**
     * @test
     */
    public function it_should_raise_incidents_for_an_account_despite_success_events_of_another_account()
    {
        $this->assertEquals(0, $this->countAllIncidents());
        $this->trigger->setSilenceTime(7);

        $this->event_logger->log($this->dummyFailure('-10 minutes', 1));
        $this->event_logger->log($this->dummySuccess('-5 minutes', 2));
        $this->event_logger->log($this->dummyFailure('now', 1));

        $this->triggering_process->run();

        $this->assertEquals(2, $this->countAllIncidents());
        $this->assertEquals(1, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_not_create_incident_if_two_accounts_fail_for_more_than_the_allowed_period_but_none_of_them_individually()
    {
        $this->assertEquals(0, $this->countAllIncidents());
        $this->trigger->setSilenceTime(7);

        $this->event_logger->log($this->dummyFailure('-12 minutes', 1));
        $this->event_logger->log($this->dummyFailure('-10 minutes', 1));
        $this->event_logger->log($this->dummyFailure('-5 minutes', 2));
        $this->event_logger->log($this->dummyFailure('now', 2));

        $this->triggering_process->run();

        $this->assertEquals(2, $this->countAllIncidents());
        $this->assertEquals(0, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_not_create_an_incident_when_success_split_failures_for_periods_shorter_than_the_allowed()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());

        $this->trigger->setSilenceTime(7);
        $this->event_logger->log($this->dummyFailure('-20 minutes'));
        $this->event_logger->log($this->dummyFailure('-19 minutes'));
        $this->event_logger->log($this->dummySuccess('-15 minutes'));
        $this->event_logger->log($this->dummyFailure('-10 minutes'));
        $this->event_logger->log($this->dummySuccess('-5 minutes'));
        $this->event_logger->log($this->dummyFailure('now'));

        $this->triggering_process->run();

        $this->assertEquals(0, $this->countRaisedIncidents());
    }

    /**
     * @test
     */
    public function it_should_create_an_incident_when_failing_for_more_than_the_allowed_interval()
    {
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->trigger->setSilenceTime(7);
        $this->event_logger->log($this->dummyFailure('-61 minutes'));
        $this->event_logger->log($this->dummyFailure('now'));

        $this->triggering_process->run();

        $this->assertEquals(1, $this->countRaisedIncidents());
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

//        $this->trigger->setSilenceTime(7);
//        $this->event_logger->log($this->dummyFailure('-10 minutes', 1));
//        $this->event_logger->log($this->dummyFailure('now', 1));
//        $this->triggering_process->run();

//        $this->assertEquals(1, $this->countRaisedIncidents());
//        $this->assertEquals(1, $logger->countMessages());
//    }

    /**
     * @test
     */
    public function it_should_not_notify_before_an_incident_is_raised()
    {
        /* @var \Swift_Plugins_MessageLogger $mailer */
        $logger = $this->get('swiftmailer.mailer.default.plugin.messagelogger');
        $logger->clear();
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->assertEquals(0, $logger->countMessages());

        $this->trigger->setSilenceTime(100);
        $this->event_logger->log($this->dummyFailure('-10 minutes', 1));
        $this->event_logger->log($this->dummyFailure('now', 1));
        $this->triggering_process->run();

        $this->assertEquals(1, $this->countAllIncidents());
        $this->assertEquals(0, $this->countRaisedIncidents());
        $this->assertEquals(0, $logger->countMessages());
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $when
     * @param int    $account_id
     *
     * @return IncomingEmailFailureEvent
     */
    protected function dummyFailure($when = 'now', $account_id = 1)
    {
        $email          = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $email->id      = $account_id;
        $email->address = 'test@dev.lo';

        $event = new IncomingEmailFailureEvent($email, new RuntimeException(), new \DateTime($when));
        $this->em->persist($event);
        $this->em->flush($event);

        return $event;
    }

    /**
     * @param string $when
     * @param int    $account_id
     *
     * @return IncomingEmailSuccessEvent
     */
    protected function dummySuccess($when = 'now', $account_id = 1)
    {
        $email          = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $email->id      = $account_id;
        $email->address = 'test@dev.lo';

        $event = new IncomingEmailSuccessEvent($email, new \DateTime($when));
        $this->em->persist($event);
        $this->em->flush($event);

        return $event;
    }
}

<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use Application\DeskPRO\Entity\EmailAccount;
use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;
use Zend\Mail\Exception\RuntimeException;

require_once realpath(__DIR__.'/../../BaseIntegrationTest.php');

/**
 * Class EmailAccountListenerIntegrationTest.
 */
class EmailAccountListenerIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var EmailAccount
     */
    private $email;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        if ($email = $this->default_em->getRepository(EmailAccount::class)->findOneBy(['address' => 'test@dev.lo'])) {
            $this->default_em->remove($email);
            $this->default_em->flush();
        }

        $this->email          = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $this->email->address = 'test@dev.lo';
        $this->default_em->persist($this->email);
        $this->default_em->flush();

        $this->event_logger->log(new IncomingEmailFailureEvent($this->email, new RuntimeException()));
        $this->event_logger->log(new IncomingEmailSuccessEvent($this->email));
        $this->event_logger->log(new OutgoingEmailFailureEvent(
            $this->email->getId(), $this->email->getAddress(), new RawTransportException()));
        $this->event_logger->log(new OutgoingEmailSuccessEvent($this->email->getId(), $this->email->getAddress()));

        // 1 not related to $this->email event producing 1 incident
        $this->event_logger->log(new OutgoingEmailFailureEvent(
            $this->email->getId() + 1, 'fake@dev.lo', new RawTransportException()));

        $this->triggering_process->run();
    }

    /**
     * @test
     */
    public function it_should_remove_all_related_events_and_incidents_if_account_email_address_has_been_changed()
    {
        $this->assertEquals(5, $this->countEvents());
        $this->assertEquals(3, $this->countAllIncidents());

        $this->email->setAddress('modified@dev.lo');
        $this->default_em->persist($this->email);
        $this->default_em->flush();

        $this->assertEquals(1, $this->countEvents());
        $this->assertEquals(1, $this->countAllIncidents());
    }

    /**
     * @test
     */
    public function it_should_remove_all_related_events_and_incidents_if_email_account_has_been_deleted()
    {
        $this->assertEquals(5, $this->countEvents());
        $this->assertEquals(3, $this->countAllIncidents());

        $this->default_em->remove($this->email);
        $this->default_em->flush();

        $this->assertEquals(1, $this->countEvents());
        $this->assertEquals(1, $this->countAllIncidents());
    }
}

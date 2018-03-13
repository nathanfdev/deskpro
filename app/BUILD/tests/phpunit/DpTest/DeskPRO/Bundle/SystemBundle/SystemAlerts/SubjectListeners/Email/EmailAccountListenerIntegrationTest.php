<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
